<?php
/**
 * Implementation of the `yii\akismet\test\ClientTest` class.
 */
namespace yii\akismet\test;

use PHPUnit\Framework\{TestCase};
use yii\akismet\{Author, Blog, Client, Comment};

/**
 * @coversDefaultClass \yii\akismet\Client
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
   * @test ::checkComment
   */
  public function testCheckComment() {
    // Should return `false` for valid comment (e.g. ham).
    $this->assertFalse($this->client->checkComment($this->ham));

    // Should return `true` for invalid comment (e.g. spam).
    $this->assertTrue($this->client->checkComment($this->spam));
  }

  /**
   * @test ::jsonSerialize
   */
  public function testJsonSerialize() {
    // Should return the right values for an incorrectly configured client.
    $data = (new Client(['apiKey' => '0123456789-ABCDEF', 'userAgent' => 'FooBar/6.6.6']))->jsonSerialize();
    $this->assertEquals('0123456789-ABCDEF', $data->apiKey);
    $this->assertNull($data->blog);
    $this->assertFalse($data->isTest);
    $this->assertEquals('FooBar/6.6.6', $data->userAgent);

    // Should return the right values for a properly configured client.
    $data = $this->client->jsonSerialize();
    $this->assertEquals(getenv('AKISMET_API_KEY'), $data->apiKey);
    $this->assertEquals(Blog::class, $data->blog);
    $this->assertTrue($data->isTest);
    $this->assertStringStartsWith('PHP/'.PHP_VERSION, $data->userAgent);
  }

  /**
   * @test ::submitHam
   */
  public function testSubmitHam() {
    // Should complete without error.
    try {
      $this->client->submitHam($this->ham);
      $this->assertTrue(true);
    }

    catch(\Throwable $e) {
      $this->fail($e->getMessage());
    }
  }

  /**
   * @test ::submitSpam
   */
  public function testSubmitSpam() {
    // Should complete without error.
    try {
      $this->client->submitSpam($this->spam);
      $this->assertTrue(true);
    }

    catch(\Throwable $e) {
      $this->fail($e->getMessage());
    }
  }

  /**
   * @test ::__toString
   */
  public function testToString() {
    $value = (string) $this->client;

    // Should start with the class name.
    $this->assertStringStartsWith('yii\akismet\Client {', $value);

    // Should contain the instance properties.
    $this->assertContains(sprintf('"apiKey":"%s"', getenv('AKISMET_API_KEY')), $value);
    $this->assertContains(sprintf('"blog":"%s"', str_replace('\\', '\\\\', Blog::class)), $value);
    $this->assertContains('"endPoint":"https://rest.akismet.com"', $value);
    $this->assertContains('"isTest":true', $value);
    $this->assertContains('"userAgent":"PHP/', $value);
  }

  /**
   * @test ::verifyKey
   */
  public function testVerifyKey() {
    // Should return `true` for a valid API key.
    $this->assertTrue($this->client->verifyKey());

    // Should return `false` for an invalid API key.
    $client = new Client(['apiKey' => '0123456789-ABCDEF', 'blog' => $this->client->getBlog(), 'isTest' => $this->client->getIsTest()]);
    $this->assertFalse($client->verifyKey());
  }

  /**
   * Performs a common set of tasks just before each test method is called.
   */
  protected function setUp() {
    $this->client = new Client([
      'apiKey' => getenv('AKISMET_API_KEY'),
      'blog' => 'https://github.com/cedx/yii2-akismet',
      'isTest' => true
    ]);

    $this->ham = \Yii::createObject([
      'class' => Comment::class,
      'author' => \Yii::createObject([
        'class' => Author::class,
        'ipAddress' => '192.168.0.1',
        'name' => 'Akismet for PHP',
        'role' => 'administrator',
        'url' => 'https://github.com/cedx/yii2-akismet',
        'userAgent' => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:42.0) Gecko/20100101 Firefox/42.0'
      ]),
      'content' => 'I\'m testing out the Service API.',
      'referrer' => 'https://packagist.org/packages/cedx/akismet',
      'type' => 'comment'
    ]);

    $this->spam = \Yii::createObject([
      'class' => Comment::class,
      'author' => \Yii::createObject([
        'class' => Author::class,
        'ipAddress' => '127.0.0.1',
        'name' => 'viagra-test-123',
        'userAgent' => 'Spam Bot/6.6.6'
      ]),
      'content' => 'Spam!',
      'type' => 'trackback'
    ]);
  }
}
