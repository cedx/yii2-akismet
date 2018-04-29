<?php
declare(strict_types=1);
namespace yii\akismet;

use function PHPUnit\Expect\{expect, fail, it};
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
   * @test Client::checkComment
   */
  public function testCheckComment(): void {
    it('should return `false` for valid comment (e.g. ham)', function() {
      expect($this->client->checkComment($this->ham))->to->be->false;
    });

    it('should return `true` for invalid comment (e.g. spam)', function() {
      expect($this->client->checkComment($this->spam))->to->be->true;
    });
  }

  /**
   * @test Client::init
   */
  public function testInit(): void {
    it('should throw an exception if the API key or blog is empty', function() {
      expect(function() { new Client; })->to->throw(InvalidConfigException::class);
    });

    it('should not throw an exception if the API key and blog are not empty', function() {
      expect(function() { new Client(['apiKey' => '0123456789-ABCDEF', 'blog' => 'FooBar']); })->to->not->throw;
    });
  }

  /**
   * @test Client::submitHam
   */
  public function testSubmitHam(): void {
    it('should complete without error', function() {
      try {
        $this->client->submitHam($this->ham);
        expect(true)->to->be->true;
      }

      catch (\Throwable $e) {
        fail($e->getMessage());
      }
    });
  }

  /**
   * @test Client::submitSpam
   */
  public function testSubmitSpam(): void {
    it('should complete without error', function() {
      try {
        $this->client->submitSpam($this->spam);
        expect(true)->to->be->true;
      }

      catch (\Throwable $e) {
        fail($e->getMessage());
      }
    });
  }

  /**
   * @test Client::verifyKey
   */
  public function testVerifyKey(): void {
    it('should return `true` for a valid API key', function() {
      expect($this->client->verifyKey())->to->be->true;
    });

    it('should return `false` for an invalid API key', function() {
      $client = new Client(['apiKey' => '0123456789-ABCDEF', 'blog' => $this->client->blog, 'isTest' => $this->client->isTest]);
      expect($client->verifyKey())->to->be->false;
    });
  }

  /**
   * Performs a common set of tasks just before each test method is called.
   */
  protected function setUp(): void {
    $this->client = new Client([
      'apiKey' => getenv('AKISMET_API_KEY'),
      'blog' => 'https://cedx.github.io/yii2-akismet',
      'isTest' => true
    ]);

    $author = new Author('192.168.0.1', 'Mozilla/5.0 (X11; Linux x86_64) Chrome/65.0.3325.181', [
      'name' => 'Akismet',
      'role' => 'administrator',
      'url' => 'https://cedx.github.io/yii2-akismet'
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
