<?php declare(strict_types=1);
namespace yii\akismet;

use Nyholm\Psr7\Uri;
use PHPUnit\Framework\{Assert, TestCase};
use yii\base\InvalidConfigException;
use function PHPUnit\Framework\{assertThat, equalTo, isFalse, isTrue, logicalOr};

/** @testdox yii\akismet\Client */
class ClientTest extends TestCase {

	/** @var Client The client used to query the service database. */
	private Client $client;

	/** @var Comment A comment with content marked as ham. */
	private Comment $ham;

	/** @var Comment A comment with content marked as spam. */
	private Comment $spam;

	/** @testdox ->checkComment() */
	function testCheckComment(): void {
		// It should return `CheckResult::isHam` for valid comment (e.g. ham).
		assertThat($this->client->checkComment($this->ham), equalTo(CheckResult::isHam));

		// It should return `CheckResult::isSpam` for invalid comment (e.g. spam).
		assertThat($this->client->checkComment($this->spam), logicalOr(
			equalTo(CheckResult::isSpam),
			equalTo(CheckResult::isPervasiveSpam)
		));
	}

	/** @testdox ->init() */
	function testInit(): void {
		// It should throw an exception if the API key or blog is empty.
		$this->expectException(InvalidConfigException::class);
		new Client;
	}

	/**
	 * @testdox ->submitHam()
	 * @doesNotPerformAssertions
	 */
	function testSubmitHam(): void {
		// It should complete without error.
		try { $this->client->submitHam($this->ham); }
		catch (\Throwable $e) { Assert::fail($e->getMessage()); }
	}

	/**
	 * @testdox ->submitSpam()
	 * @doesNotPerformAssertions
	 */
	function testSubmitSpam(): void {
		// It should complete without error.
		try { $this->client->submitSpam($this->spam); }
		catch (\Throwable $e) { Assert::fail($e->getMessage()); }
	}

	/** @testdox ->verifyKey() */
	function testVerifyKey(): void {
		// It should return `true` for a valid API key.
		assertThat($this->client->verifyKey(), isTrue());

		// It should return `false` for an invalid API key.
		$client = new Client(["apiKey" => "0123456789-ABCDEF", "blog" => $this->client->blog, "isTest" => true]);
		assertThat($client->verifyKey(), isFalse());
	}

	/** @before This method is called before each test. */
	protected function setUp(): void {
		$this->client = new Client([
			"apiKey" => getenv("AKISMET_API_KEY"),
			"blog" => new Blog(new Uri("https://dev.belin.io/yii2-akismet")),
			"isTest" => true
		]);

		$userAgent = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.97 Safari/537.36 Edg/83.0.478.45";
		$author = new Author("192.168.0.1", $userAgent, [
			"name" => "Akismet",
			"role" => "administrator",
			"url" => new Uri("https://dev.belin.io/yii2-akismet")
		]);

		$this->ham = new Comment($author, [
			"content" => "I\"m testing out the Service API.",
			"referrer" => new Uri("https://packagist.org/packages/cedx/yii2-akismet"),
			"type" => CommentType::comment
		]);

		$author = new Author("127.0.0.1", "Spam Bot/6.6.6", [
			"email" => "akismet-guaranteed-spam@example.com",
			"name" => "viagra-test-123"
		]);

		$this->spam = new Comment($author, [
			"content" => "Spam!",
			"type" => CommentType::trackback
		]);
	}
}
