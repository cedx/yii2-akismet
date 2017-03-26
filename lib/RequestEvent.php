<?php
namespace yii\akismet;

use Psr\Http\Message\{RequestInterface};
use yii\base\{Event};

/**
 * Represents `request` events triggered by the `Client` component.
 * @property RequestInterface $response The request sent by the client.
 */
class RequestEvent extends Event {

  /**
   * @var RequestInterface The request sent by the client.
   */
  private $request;

  /**
   * Gets the request sent by the client.
   * @return RequestInterface The request sent by the client.
   */
  public function getRequest() {
    return $this->request;
  }

  /**
   * Sets the request sent by the client.
   * @param RequestInterface $value The request sent by the client.
   * @return RequestEvent This instance.
   */
  public function setRequest(RequestInterface $value = null): self {
    $this->request = $value;
    return $this;
  }
}
