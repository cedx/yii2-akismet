<?php
declare(strict_types=1);
namespace yii\akismet;

use GuzzleHttp\Psr7\{Uri};
use Psr\Http\Message\{UriInterface};
use yii\base\{Component, InvalidConfigException, InvalidValueException};
use yii\helpers\{ArrayHelper};
use yii\httpclient\{Client as HttpClient, CurlTransport};
use yii\web\{ServerErrorHttpException};

/**
 * Submits comments to the [Akismet](https://akismet.com) service.
 * @property Blog $blog The front page or home URL.
 * @property UriInterface $endPoint The URL of the API end point.
 */
class Client extends Component {

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
  const EVENT_AFTER_SEND = HttpClient::EVENT_AFTER_SEND;

  /**
   * @var string An event that is triggered when a request is made to the remote service.
   */
  const EVENT_BEFORE_SEND = HttpClient::EVENT_BEFORE_SEND;

  /**
   * @var string The version number of this package.
   */
  const VERSION = '6.0.0';

  /**
   * @var string The Akismet API key.
   */
  public $apiKey = '';

  /**
   * @var bool Value indicating whether the client operates in test mode.
   */
  public $isTest = false;

  /**
   * @var string The user agent string to use when making requests.
   */
  public $userAgent = '';

  /**
   * @var Blog The front page or home URL.
   */
  private $blog;

  /**
   * @var Uri The URL of the API end point.
   */
  private $endPoint;

  /**
   * @var HttpClient The underlying HTTP client.
   */
  private $httpClient;

  /**
   * Initializes a new instance of the class.
   * @param array $config Name-value pairs that will be used to initialize the object properties.
   */
  public function __construct(array $config = []) {
    $this->httpClient = \Yii::createObject([
      'class' => HttpClient::class,
      'transport' => CurlTransport::class
    ]);

    $this->httpClient->on(HttpClient::EVENT_BEFORE_SEND, function($event) {
      $this->trigger(static::EVENT_BEFORE_SEND, $event);
    });

    $this->httpClient->on(HttpClient::EVENT_AFTER_SEND, function($event) {
      $this->trigger(static::EVENT_AFTER_SEND, $event);
    });

    parent::__construct($config);
  }

  /**
   * Checks the specified comment against the service database, and returns a value indicating whether it is spam.
   * @param Comment $comment The comment to be checked.
   * @return bool A boolean value indicating whether it is spam.
   */
  public function checkComment(Comment $comment): bool {
    $serviceUrl = parse_url((string) $this->getEndPoint());
    $endPoint = "{$serviceUrl['scheme']}://{$this->apiKey}.{$serviceUrl['host']}/1.1/comment-check";
    return $this->fetch($endPoint, \Yii::getObjectVars($comment->jsonSerialize())) == 'true';
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
   * @return UriInterface The URL of the API end point.
   */
  public function getEndPoint() {
    return $this->endPoint;
  }

  /**
   * Initializes the object.
   * @throws InvalidConfigException The API key or the blog URL is empty.
   */
  public function init(): void {
    parent::init();
    if (!mb_strlen($this->apiKey) || !$this->getBlog()) throw new InvalidConfigException('The API key or the blog URL is empty.');
    if (!$this->getEndPoint()) $this->setEndPoint(static::DEFAULT_ENDPOINT);
    if (!mb_strlen($this->userAgent)) $this->userAgent = sprintf('PHP/%s | Yii2-Akismet/%s', preg_replace('/^(\d+(\.\d+){2}).*/', '$1', PHP_VERSION), static::VERSION);
  }

  /**
   * Sets the front page or home URL of the instance making requests.
   * @param Blog|string $value The new front page or home URL.
   * @return Client This instance.
   */
  public function setBlog($value): self {
    $this->blog = is_string($value) ? new Blog($value) : $value;
    return $this;
  }

  /**
   * Sets the URL of the API end point.
   * @param string|UriInterface $value The new URL of the API end point.
   * @return Client This instance.
   */
  public function setEndPoint($value): self {
    $this->endPoint = is_string($value) ? new Uri($value) : $value;
    return $this;
  }

  /**
   * Submits the specified comment that was incorrectly marked as spam but should not have been.
   * @param Comment $comment The comment to be submitted.
   */
  public function submitHam(Comment $comment): void {
    $serviceUrl = parse_url((string) $this->getEndPoint());
    $endPoint = "{$serviceUrl['scheme']}://{$this->apiKey}.{$serviceUrl['host']}/1.1/submit-ham";
    $this->fetch($endPoint, \Yii::getObjectVars($comment->jsonSerialize()));
  }

  /**
   * Submits the specified comment that was not marked as spam but should have been.
   * @param Comment $comment The comment to be submitted.
   */
  public function submitSpam(Comment $comment): void {
    $serviceUrl = parse_url((string) $this->getEndPoint());
    $endPoint = "{$serviceUrl['scheme']}://{$this->apiKey}.{$serviceUrl['host']}/1.1/submit-spam";
    $this->fetch($endPoint, \Yii::getObjectVars($comment->jsonSerialize()));
  }

  /**
   * Checks the API key against the service database, and returns a value indicating whether it is valid.
   * @return bool A boolean value indicating whether it is a valid API key.
   */
  public function verifyKey(): bool {
    $endPoint = (string) $this->getEndPoint()->withPath('/1.1/verify-key');
    return $this->fetch($endPoint, ['key' => $this->apiKey]) == 'valid';
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
      $bodyFields = ArrayHelper::merge(\Yii::getObjectVars($this->getBlog()->jsonSerialize()), $fields);
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
