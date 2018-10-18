<?php
declare(strict_types=1);
namespace yii\akismet;

use GuzzleHttp\Psr7\{Uri};
use Psr\Http\Message\{UriInterface};
use yii\base\{Exception};

/**
 * An exception caused by an error in a `Client` request.
 * @property UriInterface $uri The URL of the HTTP request or response that failed.
 */
class ClientException extends Exception {

  /**
   * @var UriInterface The URL of the HTTP request or response that failed.
   */
  private $uri;

  /**
   * Creates a new client exception.
   * @param string $message A message describing the error.
   * @param string|UriInterface $uri The URL of the HTTP request or response that failed.
   * @param \Throwable $previous The previous exception used for the exception chaining.
   */
  function __construct($message, $uri = null, \Throwable $previous = null) {
    parent::__construct($message, $previous);
    $this->uri = is_string($uri) ? new Uri($uri) : $uri;
  }

  /**
   * Returns a string representation of this object.
   * @return string The string representation of this object.
   */
  function __toString(): string {
    $values = "'{$this->getMessage()}'";
    if ($uri = $this->getUri()) $values .= ", uri: '$uri'";
    return static::class . "($values)";
  }

  /**
   * Gets the user-friendly name of this exception.
   * @return string The user-friendly name of this exception.
   */
  function getName() {
    return 'Akismet Client Exception';
  }

  /**
   * Gets the URL of the HTTP request or response that failed.
   * @return UriInterface The URL of the HTTP request or response that failed.
   */
  function getUri(): ?UriInterface {
    return $this->uri;
  }
}
