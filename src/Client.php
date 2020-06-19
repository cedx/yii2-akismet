<?php declare(strict_types=1);
namespace yii\akismet;

use GuzzleHttp\Psr7\{Uri, UriResolver};
use Psr\Http\Message\UriInterface;
use yii\base\{Component, InvalidConfigException};
use yii\helpers\ArrayHelper;
use yii\httpclient\{Client as HttpClient, CurlTransport, Exception as HttpException};

/** Submits comments to the [Akismet](https://akismet.com) service. */
class Client extends Component {

	/** @var string An event that is triggered when a request is made to the remote service. */
	const eventRequest = "request";

	/** @var string An event that is triggered when a response is received from the remote service. */
	const eventResponse = "response";

	/** @var string The Akismet API key. */
	public string $apiKey = "";

	/** @var Blog The front page or home URL. */
	public Blog $blog;

	/** @var UriInterface The URL of the API end point. */
	public UriInterface $endPoint;

	/** @var bool Value indicating whether the client operates in test mode. */
	public bool $isTest = false;

	/** @var string The user agent string to use when making requests. */
	public string $userAgent = "";

	/** @var HttpClient The underlying HTTP client. */
	private HttpClient $http;

	/**
	 * Creates a new client.
	 * @param array<string, mixed> $config Name-value pairs that will be used to initialize the object properties.
	 */
	function __construct(array $config = []) {
		$this->http = new HttpClient(["transport" => CurlTransport::class]);
		$this->http->on(HttpClient::EVENT_BEFORE_SEND, fn($event) => $this->trigger(static::eventRequest, $event));
		$this->http->on(HttpClient::EVENT_AFTER_SEND, fn($event) => $this->trigger(static::eventResponse, $event));
		parent::__construct($config);
	}

	/**
	 * Checks the specified comment against the service database, and returns a value indicating whether it is spam.
	 * @param Comment $comment The comment to be checked.
	 * @return bool A boolean value indicating whether it is spam.
	 * @throws ClientException An error occurred while querying the end point.
	 */
	function checkComment(Comment $comment): bool {
		$host = $this->endPoint->getHost() . (($port = $this->endPoint->getPort()) ? ":$port" : "");
		$endPoint = new Uri("{$this->endPoint->getScheme()}://{$this->apiKey}.$host{$this->endPoint->getPath()}");
		return $this->fetch(UriResolver::resolve($endPoint, new Uri("comment-check")), \Yii::getObjectVars($comment->jsonSerialize())) == "true";
	}

	/**
	 * Initializes this object.
	 * @throws InvalidConfigException The API key or the blog URL is empty.
	 */
	function init(): void {
		parent::init();
		$this->endPoint ??= new Uri("https://rest.akismet.com/1.1/");
		if (!mb_strlen($this->apiKey)) throw new InvalidConfigException("The API key is empty.");

		/** @var Blog|null $blog */
		$blog = $this->blog;
		if (!$blog) throw new InvalidConfigException("The blog URL is empty.");

		if (!mb_strlen($this->userAgent)) {
			/** @var string $version */
			$version = preg_replace('/^(\d+(\.\d+){2}).*$/', '$1', \Yii::getVersion());
			$this->userAgent = sprintf("YiiFramework/%s | Akismet/%s", $version, require __DIR__."/version.g.php");
		}
	}

	/**
	 * Submits the specified comment that was incorrectly marked as spam but should not have been.
	 * @param Comment $comment The comment to be submitted.
	 * @throws ClientException An error occurred while querying the end point.
	 */
	function submitHam(Comment $comment): void {
		$host = $this->endPoint->getHost() . (($port = $this->endPoint->getPort()) ? ":$port" : "");
		$endPoint = new Uri("{$this->endPoint->getScheme()}://{$this->apiKey}.$host{$this->endPoint->getPath()}");
		$this->fetch(UriResolver::resolve($endPoint, new Uri("submit-ham")), \Yii::getObjectVars($comment->jsonSerialize()));
	}

	/**
	 * Submits the specified comment that was not marked as spam but should have been.
	 * @param Comment $comment The comment to be submitted.
	 * @throws ClientException An error occurred while querying the end point.
	 */
	function submitSpam(Comment $comment): void {
		$host = $this->endPoint->getHost() . (($port = $this->endPoint->getPort()) ? ":$port" : "");
		$endPoint = new Uri("{$this->endPoint->getScheme()}://{$this->apiKey}.$host{$this->endPoint->getPath()}");
		$this->fetch(UriResolver::resolve($endPoint, new Uri("submit-spam")), \Yii::getObjectVars($comment->jsonSerialize()));
	}

	/**
	 * Checks the API key against the service database, and returns a value indicating whether it is valid.
	 * @return bool A boolean value indicating whether it is a valid API key.
	 * @throws ClientException An error occurred while querying the end point.
	 */
	function verifyKey(): bool {
		return $this->fetch(UriResolver::resolve($this->endPoint, new Uri("verify-key")), ["key" => $this->apiKey]) == "valid";
	}

	/**
	 * Queries the service by posting the specified fields to a given end point, and returns the response as a string.
	 * @param UriInterface $endPoint The URL of the end point to query.
	 * @param array<string, string> $fields The fields describing the query body.
	 * @return string The response body.
	 * @throws ClientException An error occurred while querying the end point.
	 */
	private function fetch(UriInterface $endPoint, array $fields = []): string {
		$bodyFields = ArrayHelper::merge(\Yii::getObjectVars($this->blog->jsonSerialize()), $fields);
		if ($this->isTest) $bodyFields["is_test"] = "1";

		try { $response = $this->http->post((string) $endPoint, $bodyFields, ["user-agent" => $this->userAgent])->send(); }
		catch (HttpException $e) { throw new ClientException($e->getMessage(), $endPoint, $e); }

		if (!$response->getIsOk()) throw new ClientException($response->getStatusCode(), $endPoint);

		$headers = $response->getHeaders();
		if ($headers->has("X-akismet-debug-help")) {
			/** @var string $header */
			$header = $headers->get("X-akismet-debug-help");
			throw new ClientException($header, $endPoint);
		}

		return $response->getContent();
	}
}
