<?php
/**
 * Implementation of the `yii\akismet\test\ClientTest` class.
 */
namespace yii\akismet\test;
use yii\akismet\{Author, Blog, Client, Comment};

/**
 * Tests the features of the `yii\akismet\Client` class.
 */
class ClientTest extends \PHPUnit_Framework_TestCase {

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
   * Tests the `Client` constructor.
   */
  public function testConstructor() {
    $client = new Client(['apiKey' => '0123456789-ABCDEF', 'blog' => 'http://your.blog.url', 'userAgent' => 'FooBar/6.6.6']);
    $this->assertEquals('0123456789-ABCDEF', $client->getAPIKey());
    $this->assertEquals('FooBar/6.6.6', $client->getUserAgent());

    $blog = $client->getBlog();
    $this->assertInstanceOf(Blog::class, $blog);
    $this->assertEquals('http://your.blog.url', $blog->getURL());
  }

  /**
   * Tests the `Client::checkComment()` method.
   */
  public function testCheckComment() {
    $this->assertFalse($this->client->checkComment($this->ham));
    $this->assertTrue($this->client->checkComment($this->spam));
  }

  /**
   * Tests the `Client::jsonSerialize()` method.
   */
  public function testJsonSerialize() {
    $data = (new Client(['apiKey' => '0123456789-ABCDEF', 'userAgent' => 'FooBar/6.6.6']))->jsonSerialize();
    $this->assertEquals('0123456789-ABCDEF', $data->apiKey);
    $this->assertNull($data->blog);
    $this->assertFalse($data->test);
    $this->assertEquals('FooBar/6.6.6', $data->userAgent);

    $data = $this->client->jsonSerialize();
    $this->assertEquals(getenv('AKISMET_API_KEY'), $data->apiKey);
    $this->assertEquals(Blog::class, $data->blog);
    $this->assertTrue($data->test);
    $this->assertStringStartsWith('PHP/'.PHP_VERSION, $data->userAgent);
  }

  /**
   * Tests the `Client::submitHam()` method.
   */
  public function testSubmitHam() {
    try {
      $this->client->submitHam($this->ham);
      $this->assertTrue(true);
    }

    catch(\Throwable $e) {
      $this->fail($e->getMessage());
    }
  }

  /**
   * Tests the `Client::submitSpam()` method.
   */
  public function testSubmitSpam() {
    try {
      $this->client->submitSpam($this->spam);
      $this->assertTrue(true);
    }

    catch(\Throwable $e) {
      $this->fail($e->getMessage());
    }
  }

  /**
   * Tests the `Client::verifyKey()` method.
   */
  public function testVerifyKey() {
    $this->assertTrue($this->client->verifyKey());

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
