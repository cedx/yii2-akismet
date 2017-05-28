<?php
namespace yii\akismet;

use yii\base\{Component, InvalidConfigException, InvalidValueException};
use yii\helpers\{Json};
use yii\httpclient\{Client as HTTPClient, CurlTransport};
use yii\web\{ServerErrorHttpException};

/**
 * Submits comments to the [Akismet](https://akismet.com) service.
 * @property Blog $blog The front page or home URL.
 */
class Client extends Component implements \JsonSerializable {

  /**
   * @var string The HTTP header containing the Akismet error messages.
   */
  const DEBUG_HEADER = 'x-akismet-debug-help';

  /**
   * @var string The URL of the default API end point.
   */
  const DEFAULT_ENDPOINT = 'https://rest.akismet.com';

  /**
   * @var string An event that is triggered when a response is received from the remote service.
   */
  const EVENT_AFTER_SEND = HTTPClient::EVENT_AFTER_SEND;

  /**
   * @var string An event that is triggered when a request is made to the remote service.
   */
  const EVENT_BEFORE_SEND = HTTPClient::EVENT_BEFORE_SEND;

  /**
   * @var string The version number of this package.
   */
  const VERSION = '4.1.0';

  /**
   * @var string The Akismet API key.
   */
  public $apiKey = '';

  /**
   * @var string The URL of the API end point.
   */
  public $endPoint = self::DEFAULT_ENDPOINT;

  /**
   * @var bool Value indicating whether the client operates in test mode.
   */
  public $isTest = false;

  /**
   * @var string The user agent string to use when making requests.
   */
  public $userAgent;

  /**
   * @var Blog The front page or home URL.
   */
  private $blog;

  /**
   * @var HTTPClient The underlying HTTP client.
   */
  private $httpClient;

  /**
   * Initializes a new instance of the class.
   * @param array $config Name-value pairs that will be used to initialize the object properties.
   */
  public function __construct(array $config = []) {
    $this->httpClient = \Yii::createObject([
      'class' => HTTPClient::class,
      'transport' => CurlTransport::class
    ]);

    $this->httpClient->on(HTTPClient::EVENT_BEFORE_SEND, function($event) {
      $this->trigger(static::EVENT_BEFORE_SEND, $event);
    });

    $this->httpClient->on(HTTPClient::EVENT_AFTER_SEND, function($event) {
      $this->trigger(static::EVENT_AFTER_SEND, $event);
    });

    $this->userAgent = sprintf('PHP/%s | Yii2-Akismet/%s', preg_replace('/^(\d+(\.\d+){2}).*/', '$1', PHP_VERSION), static::VERSION);
    parent::__construct($config);
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
    $serviceURL = parse_url($this->endPoint);
    $endPoint = sprintf('%s://%s.%s/1.1/comment-check', $serviceURL['scheme'], $this->apiKey, $serviceURL['host']);
    return $this->fetch($endPoint, get_object_vars($comment->jsonSerialize())) == 'true';
  }

  /**
   * Gets the front page or home URL of the instance making requests.
   * @return Blog The front page or home URL.
   */
  public function getBlog() {
    return $this->blog;
  }

  /**
   * Initializes the object.
   * @throws InvalidConfigException The API key or the blog URL is empty.
   */
  public function init() {
    parent::init();
    if (!mb_strlen($this->apiKey) || !$this->getBlog()) throw new InvalidConfigException('The API key or the blog URL is empty.');
  }

  /**
   * Converts this object to a map in JSON format.
   * @return \stdClass The map in JSON format corresponding to this object.
   */
  public function jsonSerialize(): \stdClass {
    return (object) [
      'apiKey' => $this->apiKey,
      'blog' => ($blog = $this->getBlog()) ? get_class($blog) : null,
      'endPoint' => $this->endPoint,
      'isTest' => $this->isTest,
      'userAgent' => $this->userAgent
    ];
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

    return $this;
  }

  /**
   * Submits the specified comment that was incorrectly marked as spam but should not have been.
   * @param Comment $comment The comment to be submitted.
   */
  public function submitHam(Comment $comment) {
    $serviceURL = parse_url($this->endPoint);
    $endPoint = sprintf('%s://%s.%s/1.1/submit-ham', $serviceURL['scheme'], $this->apiKey, $serviceURL['host']);
    $this->fetch($endPoint, get_object_vars($comment->jsonSerialize()));
  }

  /**
   * Submits the specified comment that was not marked as spam but should have been.
   * @param Comment $comment The comment to be submitted.
   */
  public function submitSpam(Comment $comment) {
    $serviceURL = parse_url($this->endPoint);
    $endPoint = sprintf('%s://%s.%s/1.1/submit-spam', $serviceURL['scheme'], $this->apiKey, $serviceURL['host']);
    $this->fetch($endPoint, get_object_vars($comment->jsonSerialize()));
  }

  /**
   * Checks the API key against the service database, and returns a value indicating whether it is valid.
   * @return bool A boolean value indicating whether it is a valid API key.
   */
  public function verifyKey(): bool {
    return $this->fetch("{$this->endPoint}/1.1/verify-key", ['key' => $this->apiKey]) == 'valid';
  }

  /**
   * Queries the service by posting the specified fields to a given end point, and returns the response as a string.
   * @param string $endPoint The URL of the end point to query.
   * @param array $fields The fields describing the query body.
   * @return string The response body.
   * @emits \yii\httpclient\RequestEvent The "beforeSend" event.
   * @emits \yii\httpclient\RequestEvent The "afterSend" event.
   * @throws ServerErrorHttpException An error occurred while querying the end point.
   */
  private function fetch(string $endPoint, array $fields = []): string {
    try {
      $bodyFields = array_merge(get_object_vars($this->getBlog()->jsonSerialize()), $fields);
      if ($this->isTest) $bodyFields['is_test'] = '1';

      $response = $this->httpClient->post($endPoint, $bodyFields, ['User-Agent' => $this->userAgent])->send();
      if (!$response->isOk) throw new InvalidValueException($response->statusCode);
      if ($response->headers->has(static::DEBUG_HEADER)) throw new InvalidValueException($response->headers->get(static::DEBUG_HEADER));
      return $response->content;
    }

    catch (\Throwable $e) {
      throw new ServerErrorHttpException($e->getMessage());
    }
  }
}
