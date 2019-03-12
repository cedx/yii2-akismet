<?php
declare(strict_types=1);
namespace yii\akismet;

use function League\Uri\parse as parseUri;
use League\Uri\{Http as Uri};
use yii\base\{Component, InvalidConfigException};
use yii\helpers\{ArrayHelper};
use yii\httpclient\{Client as HttpClient, CurlTransport, Exception as HttpException};

/**
 * Submits comments to the [Akismet](https://akismet.com) service.
 * @property Blog $blog The front page or home URL.
 * @property Uri $endPoint The URL of the API end point.
 */
class Client extends Component {

  /**
   * @var string An event that is triggered when a request is made to the remote service.
   */
  const EVENT_REQUEST = 'request';

  /**
   * @var string An event that is triggered when a response is received from the remote service.
   */
  const EVENT_RESPONSE = 'response';

  /**
   * @var string The version number of this package.
   */
  const VERSION = '7.1.0';

  /**
   * @var string The HTTP header containing the Akismet error messages.
   */
  private const DEBUG_HEADER = 'x-akismet-debug-help';

  /**
   * @var string The URL of the default API end point.
   */
  private const DEFAULT_ENDPOINT = 'https://rest.akismet.com';

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
   * Creates a new client.
   * @param array $config Name-value pairs that will be used to initialize the object properties.
   */
  function __construct(array $config = []) {
    $this->httpClient = new HttpClient(['transport' => CurlTransport::class]);

    $this->httpClient->on(HttpClient::EVENT_BEFORE_SEND, function($event) {
      $this->trigger(static::EVENT_REQUEST, $event);
    });

    $this->httpClient->on(HttpClient::EVENT_AFTER_SEND, function($event) {
      $this->trigger(static::EVENT_RESPONSE, $event);
    });

    parent::__construct($config);
  }

  /**
   * Checks the specified comment against the service database, and returns a value indicating whether it is spam.
   * @param Comment $comment The comment to be checked.
   * @return bool A boolean value indicating whether it is spam.
   * @throws ClientException An error occurred while querying the end point.
   */
  function checkComment(Comment $comment): bool {
    $serviceUrl = parseUri((string) $this->getEndPoint());
    $endPoint = "{$serviceUrl['scheme']}://{$this->apiKey}.{$serviceUrl['host']}/1.1/comment-check";
    return $this->fetch($endPoint, \Yii::getObjectVars($comment->jsonSerialize())) == 'true';
  }

  /**
   * Gets the front page or home URL of the instance making requests.
   * @return Blog The front page or home URL.
   */
  function getBlog(): ?Blog {
    return $this->blog;
  }

  /**
   * Gets the URL of the API end point.
   * @return Uri The URL of the API end point.
   */
  function getEndPoint(): Uri {
    return $this->endPoint;
  }

  /**
   * Initializes the object.
   * @throws InvalidConfigException The API key or the blog URL is empty.
   */
  function init(): void {
    parent::init();
    if (!mb_strlen($this->apiKey) || !$this->getBlog()) throw new InvalidConfigException('The API key or the blog URL is empty.');
    if (!$this->getEndPoint()) $this->setEndPoint(static::DEFAULT_ENDPOINT);
    if (!mb_strlen($this->userAgent))
      $this->userAgent = sprintf('Yii Framework/%s | Akismet/%s', preg_replace('/^(\d+(\.\d+){2}).*$/', '$1', \Yii::getVersion()), static::VERSION);
  }

  /**
   * Sets the front page or home URL of the instance making requests.
   * @param Blog|string $value The new front page or home URL.
   * @return $this This instance.
   */
  function setBlog($value): self {
    $this->blog = is_string($value) ? new Blog($value) : $value;
    return $this;
  }

  /**
   * Sets the URL of the API end point.
   * @param Uri|string $value The new URL of the API end point.
   * @return $this This instance.
   */
  function setEndPoint($value): self {
    $this->endPoint = is_string($value) ? Uri::createFromString($value) : $value;
    return $this;
  }

  /**
   * Submits the specified comment that was incorrectly marked as spam but should not have been.
   * @param Comment $comment The comment to be submitted.
   * @throws ClientException An error occurred while querying the end point.
   */
  function submitHam(Comment $comment): void {
    $serviceUrl = parseUri((string) $this->getEndPoint());
    $endPoint = "{$serviceUrl['scheme']}://{$this->apiKey}.{$serviceUrl['host']}/1.1/submit-ham";
    $this->fetch($endPoint, \Yii::getObjectVars($comment->jsonSerialize()));
  }

  /**
   * Submits the specified comment that was not marked as spam but should have been.
   * @param Comment $comment The comment to be submitted.
   * @throws ClientException An error occurred while querying the end point.
   */
  function submitSpam(Comment $comment): void {
    $serviceUrl = parseUri((string) $this->getEndPoint());
    $endPoint = "{$serviceUrl['scheme']}://{$this->apiKey}.{$serviceUrl['host']}/1.1/submit-spam";
    $this->fetch($endPoint, \Yii::getObjectVars($comment->jsonSerialize()));
  }

  /**
   * Checks the API key against the service database, and returns a value indicating whether it is valid.
   * @return bool A boolean value indicating whether it is a valid API key.
   * @throws ClientException An error occurred while querying the end point.
   */
  function verifyKey(): bool {
    $endPoint = (string) $this->getEndPoint()->withPath('/1.1/verify-key');
    return $this->fetch($endPoint, ['key' => $this->apiKey]) == 'valid';
  }

  /**
   * Queries the service by posting the specified fields to a given end point, and returns the response as a string.
   * @param string $endPoint The URL of the end point to query.
   * @param array $fields The fields describing the query body.
   * @return string The response body.
   * @throws ClientException An error occurred while querying the end point.
   */
  private function fetch(string $endPoint, array $fields = []): string {
      $bodyFields = ArrayHelper::merge(\Yii::getObjectVars($this->getBlog()->jsonSerialize()), $fields);
      if ($this->isTest) $bodyFields['is_test'] = '1';

      try { $response = $this->httpClient->post($endPoint, $bodyFields, ['user-agent' => $this->userAgent])->send(); }
      catch (HttpException $e) { throw new ClientException($e->getMessage(), $endPoint, $e); }

      if (!$response->isOk) throw new ClientException($response->statusCode, $endPoint);
      if ($response->headers->has(static::DEBUG_HEADER)) throw new ClientException($response->headers->get(static::DEBUG_HEADER), $endPoint);
      return $response->content;
  }
}
