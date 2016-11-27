<?php
/**
 * Implementation of the `yii\akismet\ResponseEvent` class.
 */
namespace yii\akismet;

use Psr\Http\Message\{ResponseInterface};
use yii\base\{Event};

/**
 * Represents `response` events triggered by the `Client` component.
 */
class ResponseEvent extends Event {

  /**
   * @var ResponseInterface The response received by the client.
   */
  private $response;

  /**
   * Gets the response received by the client.
   * @return ResponseInterface The response received by the client.
   */
  public function getResponse() {
    return $this->response;
  }

  /**
   * Sets the response received by the client.
   * @param ResponseInterface $value The response received by the client.
   * @return ResponseEvent This instance.
   */
  public function setResponse(ResponseInterface $value = null): self {
    $this->response = $value;
    return $this;
  }
}
