<?php
namespace yii\akismet;

use akismet\{Blog as AkismetBlog, Client as AkismetClient, Comment as AkismetComment};
use yii\base\{Component};
use yii\helpers\{Json};

/**
 * Submits comments to the [Akismet](https://akismet.com) service.
 * @property string $apiKey The Akismet API key.
 * @property Blog $blog The front page or home URL.
 * @property string $endPoint The URL of the API end point.
 * @property bool $isTest Value indicating whether the client operates in test mode.
 * @property string $userAgent The user agent string to use when making requests.
 */
class Client extends Component implements \JsonSerializable {

  /**
   * @var string An event that is triggered when a request is made to the remote service.
   */
  const EVENT_REQUEST = 'request';

  /**
   * @var string An event that is triggered when a response is received from the remote service.
   */
  const EVENT_RESPONSE = 'reponse';

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

    $this->client->on('request', function($request) {
      $this->trigger(static::EVENT_REQUEST, new RequestEvent(['request' => $request]));
    });

    $this->client->on('response', function($response) {
      $this->trigger(static::EVENT_RESPONSE, new ResponseEvent(['response' => $response]));
    });
  }

  /**
   * Returns a string representation of this object.
   * @return string The string representation of this object.
   */
  public function __toString(): string {
    $json = Json::encode($this);
    return static::class." $json";
  }

  /**
   * Checks the specified comment against the service database, and returns a value indicating whether it is spam.
   * @param Comment $comment The comment to be checked.
   * @return bool A boolean value indicating whether it is spam.
   */
  public function checkComment(Comment $comment): bool {
    $model = AkismetComment::fromJSON($comment->jsonSerialize());
    return $this->client->checkComment($model);
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
   * Gets the URL of the API end point.
   * @return string The URL of the API end point.
   */
  public function getEndPoint(): string {
    return $this->client->getEndPoint();
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
    if ($blog = $this->getBlog()) $map->blog = get_class($blog);
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
    else if (is_string($value)) $this->blog = new Blog(['url' => $value]);
    else $this->blog = null;

    $this->client->setBlog(AkismetBlog::fromJSON($this->blog ? $this->blog->jsonSerialize() : null));
    return $this;
  }

  /**
   * Sets the URL of the API end point.
   * @param string $value The new URL of the API end point.
   * @return Client This instance.
   */
  public function setEndPoint(string $value) {
    $this->client->setEndPoint($value);
    return $this;
  }

  /**
   * Sets a value indicating whether the client operates in test mode.
   * You can use it when submitting test queries to Akismet.
   * @param bool $value `true` to enable the test mode, otherwise `false`.
   * @return Client This instance.
   */
  public function setIsTest(bool $value): self {
    $this->client->setIsTest($value);
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
    $this->client->submitHam($model);
  }

  /**
   * Submits the specified comment that was not marked as spam but should have been.
   * @param Comment $comment The comment to be submitted.
   */
  public function submitSpam(Comment $comment) {
    $model = AkismetComment::fromJSON($comment->jsonSerialize());
    $this->client->submitSpam($model);
  }

  /**
   * Checks the API key against the service database, and returns a value indicating whether it is valid.
   * @return bool A boolean value indicating whether it is a valid API key.
   */
  public function verifyKey(): bool {
    return $this->client->verifyKey();
  }
}
