<?php declare(strict_types=1);
namespace yii\akismet;

use PHPUnit\Framework\{TestCase};
use yii\base\{InvalidConfigException};

/**
 * Tests the features of the `yii\akismet\Client` class.
 */
class ClientTest extends TestCase {

  /**
   * @var Client The client used to query the service database.
   */
  private $client;

  /**
   * @var Comment A comment with content marked as ham.
   */
  private $ham;

  /**
   * @var Comment A comment with content marked as spam.
   */
  private $spam;

  /**
   * Tests the `Client::checkComment()` method.
   */
  function testCheckComment(): void {
    // It should return `false` for valid comment (e.g. ham).
    assertThat($this->client->checkComment($this->ham), isFalse());

    // It should return `true` for invalid comment (e.g. spam).
    assertThat($this->client->checkComment($this->spam), isTrue());
  }

  /**
   * Tests the `Client::init()` method.
   */
  function testInit(): void {
    // It should throw an exception if the API key or blog is empty.
    try {
      new Client;
      $this->fail('Exception not thrown.');
    }

    catch (\Throwable $e) {
      assertThat($e, isInstanceOf(InvalidConfigException::class));
    }

    // It should not throw an exception if the API key and blog are not empty.
    try {
      new Client(['apiKey' => '0123456789-ABCDEF', 'blog' => 'FooBar']);
      assertThat(true, isTrue());
    }

    catch (\Throwable $e) {
      $this->fail($e->getMessage());
    }
  }

  /**
   * Tests the `Client::submitHam()` method.
   */
  function testSubmitHam(): void {
    // It should complete without error.
    try {
      $this->client->submitHam($this->ham);
      assertThat(true, isTrue());
    }

    catch (\Throwable $e) {
      $this->fail($e->getMessage());
    }
  }

  /**
   * Tests the `Client::submitSpam()` method.
   */
  function testSubmitSpam(): void {
    // It should complete without error.
    try {
      $this->client->submitSpam($this->spam);
      assertThat(true, isTrue());
    }

    catch (\Throwable $e) {
      $this->fail($e->getMessage());
    }
  }

  /**
   * Tests the `Client::verifyKey()` method.
   */
  function testVerifyKey(): void {
    // It should return `true` for a valid API key.
    assertThat($this->client->verifyKey(), isTrue());

    // It should return `false` for an invalid API key.
    $client = new Client(['apiKey' => '0123456789-ABCDEF', 'blog' => $this->client->blog, 'isTest' => $this->client->isTest]);
    assertThat($client->verifyKey(), isFalse());
  }

  /**
   * This method is called before each test.
   * @before
   */
  protected function setUp(): void {
    $this->client = new Client([
      'apiKey' => getenv('AKISMET_API_KEY'),
      'blog' => 'https://dev.belin.io/yii2-akismet',
      'isTest' => true
    ]);

    $author = new Author('192.168.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:66.0) Gecko/20100101 Firefox/66.0', [
      'name' => 'Akismet',
      'role' => 'administrator',
      'url' => 'https://dev.belin.io/yii2-akismet'
    ]);

    $this->ham = new Comment($author, [
      'content' => 'I\'m testing out the Service API.',
      'referrer' => 'https://packagist.org/packages/cedx/yii2-akismet',
      'type' => CommentType::COMMENT
    ]);

    $author = new Author('127.0.0.1', 'Spam Bot/6.6.6', [
      'email' => 'akismet-guaranteed-spam@example.com',
      'name' => 'viagra-test-123'
    ]);

    $this->spam = new Comment($author, [
      'content' => 'Spam!',
      'type' => CommentType::TRACKBACK
    ]);
  }
}
