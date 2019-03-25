<?php declare(strict_types=1);
namespace yii\akismet;

use function League\Uri\{create as createUri};
use League\Uri\{UriInterface};
use yii\base\{Component, InvalidConfigException};
use yii\helpers\{ArrayHelper};
use yii\httpclient\{Client as HttpClient, CurlTransport, Exception as HttpException};

/**
 * Submits comments to the [Akismet](https://akismet.com) service.
 * @property Blog $blog The front page or home URL.
 * @property UriInterface $endPoint The URL of the API end point.
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
   * @var string The Akismet API key.
   */
  public $apiKey = '';

  /**
   * @var Blog The front page or home URL.
   */
  public $blog;

  /**
   * @var UriInterface The URL of the API end point.
   */
  public $endPoint;

  /**
   * @var bool Value indicating whether the client operates in test mode.
   */
  public $isTest = false;

  /**
   * @var string The user agent string to use when making requests.
   */
  public $userAgent = '';

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
    $this->httpClient->on(HttpClient::EVENT_BEFORE_SEND, function($event) { $this->trigger(static::EVENT_REQUEST, $event); });
    $this->httpClient->on(HttpClient::EVENT_AFTER_SEND, function($event) { $this->trigger(static::EVENT_RESPONSE, $event); });
    parent::__construct($config);
  }

  /**
   * Checks the specified comment against the service database, and returns a value indicating whether it is spam.
   * @param Comment $comment The comment to be checked.
   * @return bool A boolean value indicating whether it is spam.
   * @throws ClientException An error occurred while querying the end point.
   */
  function checkComment(Comment $comment): bool {
    $endPoint = "{$this->endPoint->getScheme()}://{$this->apiKey}.{$this->endPoint->getHost()}/1.1/comment-check";
    return $this->fetch(createUri($endPoint), \Yii::getObjectVars($comment->jsonSerialize())) == 'true';
  }

  /**
   * Initializes the object.
   * @throws InvalidConfigException The API key or the blog URL is empty.
   */
  function init(): void {
    parent::init();
    if (!mb_strlen($this->apiKey) || !$this->blog) throw new InvalidConfigException('The API key or the blog URL is empty.');
    if (!$this->endPoint) $this->endPoint = createUri('https://rest.akismet.com');
    if (!mb_strlen($this->userAgent))
      $this->userAgent = sprintf('Yii Framework/%s | Akismet/%s', preg_replace('/^(\d+(\.\d+){2}).*$/', '$1', \Yii::getVersion()), static::VERSION);
  }

  /**
   * Submits the specified comment that was incorrectly marked as spam but should not have been.
   * @param Comment $comment The comment to be submitted.
   * @throws ClientException An error occurred while querying the end point.
   */
  function submitHam(Comment $comment): void {
    $endPoint = "{$this->endPoint->getScheme()}://{$this->apiKey}.{$this->endPoint->getHost()}/1.1/submit-ham";
    $this->fetch(createUri($endPoint), \Yii::getObjectVars($comment->jsonSerialize()));
  }

  /**
   * Submits the specified comment that was not marked as spam but should have been.
   * @param Comment $comment The comment to be submitted.
   * @throws ClientException An error occurred while querying the end point.
   */
  function submitSpam(Comment $comment): void {
    $endPoint = "{$this->endPoint->getScheme()}://{$this->apiKey}.{$this->endPoint->getHost()}/1.1/submit-spam";
    $this->fetch(createUri($endPoint), \Yii::getObjectVars($comment->jsonSerialize()));
  }

  /**
   * Checks the API key against the service database, and returns a value indicating whether it is valid.
   * @return bool A boolean value indicating whether it is a valid API key.
   * @throws ClientException An error occurred while querying the end point.
   */
  function verifyKey(): bool {
    return $this->fetch($this->endPoint->withPath('/1.1/verify-key'), ['key' => $this->apiKey]) == 'valid';
  }

  /**
   * Queries the service by posting the specified fields to a given end point, and returns the response as a string.
   * @param UriInterface $endPoint The URL of the end point to query.
   * @param array $fields The fields describing the query body.
   * @return string The response body.
   * @throws ClientException An error occurred while querying the end point.
   */
  private function fetch(UriInterface $endPoint, array $fields = []): string {
      $bodyFields = ArrayHelper::merge(\Yii::getObjectVars($this->blog->jsonSerialize()), $fields);
      if ($this->isTest) $bodyFields['is_test'] = '1';

      try { $response = $this->httpClient->post((string) $endPoint, $bodyFields, ['user-agent' => $this->userAgent])->send(); }
      catch (HttpException $e) { throw new ClientException($e->getMessage(), $endPoint, $e); }

      if (!$response->isOk) throw new ClientException($response->statusCode, $endPoint);
      if ($response->headers->has('x-akismet-debug-help')) throw new ClientException($response->headers->get('x-akismet-debug-help'), $endPoint);
      return $response->content;
  }
}
