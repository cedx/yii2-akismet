<?php
namespace yii\akismet;

use yii\base\{Component};
use yii\helpers\{Json};

/**
 * Submits comments to the [Akismet](https://akismet.com) service.
 * @property Blog $blog The front page or home URL.
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
   * @var string The Akismet API key.
   */
  public $apiKey = '';

  /**
   * @var string The URL of the API end point.
   */
  public $endPoint = 'TODO';

  /**
   * @var bool Value indicating whether the client operates in test mode.
   */
  public $isTest = false;

  /**
   * @var string The user agent string to use when making requests.
   */
  public $userAgent = 'TODO';

  /**
   * @var Blog The front page or home URL.
   */
  private $blog;

  /**
   * Initializes a new instance of the class.
   * @param array $config Name-value pairs that will be used to initialize the object properties.
   */
  public function __construct(array $config = []) { // TODO: replace by init() method.
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
   * Gets the front page or home URL of the instance making requests.
   * @return Blog The front page or home URL.
   */
  public function getBlog() {
    return $this->blog;
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
