<?php
namespace yii\akismet;
use PHPUnit\Framework\{TestCase};

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
  public function testCheckComment() {
    it('should return `false` for valid comment (e.g. ham)', function() {
      expect($this->client->checkComment($this->ham))->to->be->false;
    });

    it('should return `true` for invalid comment (e.g. spam)', function() {
      expect($this->client->checkComment($this->spam))->to->be->true;
    });
  }

  /**
   * @test Client::jsonSerialize
   */
  public function testJsonSerialize() {
    it('should return the right values for an incorrectly configured client', function() {
      $data = (new Client(['apiKey' => '0123456789-ABCDEF', 'userAgent' => 'FooBar/6.6.6']))->jsonSerialize();
      expect($data->apiKey)->to->equal('0123456789-ABCDEF');
      expect($data->blog)->to->be->null;
      expect($data->isTest)->to->be->false;
      expect($data->userAgent)->to->equal('FooBar/6.6.6');
    });

    it('should return the right values for a properly configured client', function() {
      $data = $this->client->jsonSerialize();
      expect($data->apiKey)->to->equal(getenv('AKISMET_API_KEY'));
      expect($data->blog)->to->equal(Blog::class);
      expect($data->isTest)->to->be->true;
      expect($data->userAgent)->to->startWith('PHP/'.PHP_VERSION);
    });
  }

  /**
   * @test Client::submitHam
   */
  public function testSubmitHam() {
    it('should complete without error', function() {
      try {
        $this->client->submitHam($this->ham);
        expect(true)->to->be->true;
      }

      catch(\Throwable $e) {
        fail($e->getMessage());
      }
    });
  }

  /**
   * @test Client::submitSpam
   */
  public function testSubmitSpam() {
    it('should complete without error', function() {
      try {
        $this->client->submitSpam($this->spam);
        expect(true)->to->be->true;
      }

      catch(\Throwable $e) {
        fail($e->getMessage());
      }
    });
  }

  /**
   * @test Client::__toString
   */
  public function testToString() {
    $value = (string) $this->client;

    it('should start with the class name', function() use ($value) {
      expect($value)->to->startWith('yii\akismet\Client {');
    });

    it('should contain the instance properties', function() use ($value) {
      expect($value)->to->contain(sprintf('"apiKey":"%s"', getenv('AKISMET_API_KEY')))
        ->and->contain(sprintf('"blog":"%s"', str_replace('\\', '\\\\', Blog::class)))
        ->and->contain('"endPoint":"https://rest.akismet.com"')
        ->and->contain('"isTest":true')
        ->and->contain('"userAgent":"PHP/');
    });
  }

  /**
   * @test Client::verifyKey
   */
  public function testVerifyKey() {
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
  protected function setUp() {
    $this->client = new Client([
      'apiKey' => getenv('AKISMET_API_KEY'),
      'blog' => 'https://github.com/cedx/yii2-akismet',
      'isTest' => true
    ]);

    $this->ham = new Comment([
      'author' => new Author([
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

    $this->spam = new Comment([
      'author' => new Author([
        'ipAddress' => '127.0.0.1',
        'name' => 'viagra-test-123',
        'userAgent' => 'Spam Bot/6.6.6'
      ]),
      'content' => 'Spam!',
      'type' => 'trackback'
    ]);
  }
}
