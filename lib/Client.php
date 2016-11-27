<?php
/**
 * Implementation of the `yii\akismet\Client` class.
 */
namespace yii\akismet;

use akismet\{Blog as AkismetBlog, Client as AkismetClient, Comment as AkismetComment};
use yii\base\{Component, Exception};

/**
 * Submits comments to the [Akismet](https://akismet.com) service.
 */
class Client extends Component implements \JsonSerializable {

  /**
   * @var Blog The front page or home URL.
   */
  private $blog;

  /**
   * @var AkismetClient The underlying client.
   */
  private $client;

  /**
   * Initializes a new instance of the class.
   * @param array $config Name-value pairs that will be used to initialize the object properties.
   */
  public function __construct(array $config = []) {
    $this->client = new AkismetClient();
    parent::__construct($config);
  }

  /**
   * Returns a string representation of this object.
   * @return string The string representation of this object.
   */
  public function __toString(): string {
    $json = json_encode($this, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    return static::class." {$json}";
  }

  /**
   * Checks the specified comment against the service database, and returns a value indicating whether it is spam.
   * @param Comment $comment The comment to be checked.
   * @return bool A boolean value indicating whether it is spam.
   */
  public function checkComment(Comment $comment): bool {
    $model = AkismetComment::fromJSON($comment->jsonSerialize());
    $this->client->checkComment($model)->subscribeCallback(
      function(bool $response) use (&$result) { $result = $response; },
      function(\Throwable $e) { throw new Exception($e->getMessage(), $e->getCode(), $e); }
    );

    return $result;
  }

  /**
   * Gets the Akismet API key.
   * @return string The Akismet API key.
   */
  public function getAPIKey(): string {
    return $this->client->getAPIKey();
  }

  /**
   * Gets the front page or home URL of the instance making requests.
   * @return Blog The front page or home URL.
   */
  public function getBlog() {
    return $this->blog;
  }

  /**
   * Gets a value indicating whether the client operates in test mode.
   * @return bool `true` if the client operates in test mode, otherwise `false`.
   */
  public function getIsTest(): bool {
    return $this->client->isTest();
  }

  /**
   * Gets the user agent string to use when making requests.
   * @return string The user agent string to use when making requests.
   */
  public function getUserAgent(): string {
    return $this->client->getUserAgent();
  }

  /**
   * Converts this object to a map in JSON format.
   * @return \stdClass The map in JSON format corresponding to this object.
   */
  public function jsonSerialize(): \stdClass {
    $map = $this->client->jsonSerialize();
    if (isset($map->blog)) $map->blog = get_class($this->getBlog());
    return $map;
  }

  /**
   * Sets the Akismet API key.
   * @param string $value The new API key.
   * @return Client This instance.
   */
  public function setAPIKey(string $value): self {
    $this->client->setAPIKey($value);
    return $this;
  }

  /**
   * Sets the front page or home URL of the instance making requests.
   * @param Blog|string $value The new front page or home URL.
   * @return Client This instance.
   */
  public function setBlog($value): self {
    if ($value instanceof Blog) $this->blog = $value;
    else if (is_string($value)) $this->blog = \Yii::createObject(['class' => Blog::class, 'url' => $value]);
    else $this->blog = null;

    $this->client->setBlog($this->blog ? AkismetBlog::fromJSON($this->blog->jsonSerialize()) : null);
    return $this;
  }

  /**
   * Sets a value indicating whether the client operates in test mode.
   * You can use it when submitting test queries to Akismet.
   * @param bool $value `true` to enable the test mode, otherwise `false`.
   * @return Client This instance.
   */
  public function setIsTest(bool $value): self {
    $this->client->setTest($value);
    return $this;
  }

  /**
   * Sets the user agent string to use when making requests.
   * If possible, the user agent string should always have the following format: `Application Name/Version | Plugin Name/Version`.
   * @param string $value The new user agent string.
   * @return Client This instance.
   */
  public function setUserAgent(string $value): self {
    $this->client->setUserAgent($value);
    return $this;
  }

  /**
   * Submits the specified comment that was incorrectly marked as spam but should not have been.
   * @param Comment $comment The comment to be submitted.
   */
  public function submitHam(Comment $comment) {
    $model = AkismetComment::fromJSON($comment->jsonSerialize());
    $this->client->submitHam($model)->subscribeCallback(
      null,
      function(\Throwable $e) { throw new Exception($e->getMessage(), $e->getCode(), $e); }
    );
  }

  /**
   * Submits the specified comment that was not marked as spam but should have been.
   * @param Comment $comment The comment to be submitted.
   */
  public function submitSpam(Comment $comment) {
    $model = AkismetComment::fromJSON($comment->jsonSerialize());
    $this->client->submitSpam($model)->subscribeCallback(
      null,
      function(\Throwable $e) { throw new Exception($e->getMessage(), $e->getCode(), $e); }
    );
  }

  /**
   * Checks the API key against the service database, and returns a value indicating whether it is valid.
   * @return bool A boolean value indicating whether it is a valid API key.
   */
  public function verifyKey(): bool {
    $this->client->verifyKey()->subscribeCallback(
      function(bool $response) use (&$result) { $result = $response; },
      function(\Throwable $e) { throw new Exception($e->getMessage(), $e->getCode(), $e); }
    );

    return $result;
  }
}
