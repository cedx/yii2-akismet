<?php declare(strict_types=1);
namespace yii\akismet;

use function PHPUnit\Expect\{expect, it};
use GuzzleHttp\Psr7\{Uri};
use PHPUnit\Framework\{TestCase};
use yii\base\{InvalidConfigException};

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
    it('should return `false` for valid comment (e.g. ham)', function() {
      expect($this->client->checkComment($this->ham))->to->be->false;
    });

    it('should return `true` for invalid comment (e.g. spam)', function() {
      expect($this->client->checkComment($this->spam))->to->be->true;
    });
  }

  /** @testdox ->init() */
  function testInit(): void {
    it('should throw an exception if the API key or blog is empty', function() {
      expect(fn() => new Client)->to->throw(InvalidConfigException::class);
    });

    it('should not throw an exception if the API key and blog are not empty', function() {
      expect(fn() => new Client(['apiKey' => '0123456789-ABCDEF', 'blog' => 'FooBar']))->to->not->throw;
    });
  }

  /** @testdox ->submitHam() */
  function testSubmitHam(): void {
    it('should complete without error', function() {
      expect(fn() => $this->client->submitHam($this->ham))->to->not->throw;
    });
  }

  /** @testdox ->submitSpam() */
  function testSubmitSpam(): void {
    it('should complete without error', function() {
      expect(fn() => $this->client->submitSpam($this->spam))->to->not->throw;
    });
  }

  /** @testdox ->verifyKey() */
  function testVerifyKey(): void {
    it('should return `true` for a valid API key', function() {
      expect($this->client->verifyKey())->to->be->true;
    });

    it('should return `false` for an invalid API key', function() {
      $client = new Client(['apiKey' => '0123456789-ABCDEF', 'blog' => $this->client->blog, 'isTest' => $this->client->isTest]);
      expect($client->verifyKey())->to->be->false;
    });
  }

  /** @before This method is called before each test. */
  protected function setUp(): void {
    $this->client = new Client([
      'apiKey' => getenv('AKISMET_API_KEY'),
      'blog' => new Blog(new Uri('https://dev.belin.io/yii2-akismet')),
      'isTest' => true
    ]);

    $author = new Author('192.168.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:74.0) Gecko/20100101 Firefox/74.0', [
      'name' => 'Akismet',
      'role' => 'administrator',
      'url' => new Uri('https://dev.belin.io/yii2-akismet')
    ]);

    $this->ham = new Comment($author, [
      'content' => 'I\'m testing out the Service API.',
      'referrer' => new Uri('https://packagist.org/packages/cedx/yii2-akismet'),
      'type' => CommentType::comment
    ]);

    $author = new Author('127.0.0.1', 'Spam Bot/6.6.6', [
      'email' => 'akismet-guaranteed-spam@example.com',
      'name' => 'viagra-test-123'
    ]);

    $this->spam = new Comment($author, [
      'content' => 'Spam!',
      'type' => CommentType::trackback
    ]);
  }
}
