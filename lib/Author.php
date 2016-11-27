<?php
/**
 * Implementation of the `yii\akismet\Author` class.
 */
namespace yii\akismet;

use akismet\{Author as AkismetAuthor};
use yii\base\{Object};

/**
 * Represents the author of a comment.
 */
class Author extends Object implements \JsonSerializable {

  /**
   * @var AkismetAuthor The underlying Akismet author.
   */
  private $author;

  /**
   * Initializes a new instance of the class.
   * @param array $config Name-value pairs that will be used to initialize the object properties.
   */
  public function __construct(array $config = []) {
    $this->author = new AkismetAuthor();
    parent::__construct($config);
  }

  /**
   * Returns a string representation of this object.
   * @return string The string representation of this object.
   */
  public function __toString(): string {
    $json = json_encode($this, JSON_FORCE_OBJECT | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    return static::class." {$json}";
  }

  /**
   * Gets the author's mail address.
   * @return string The author's mail address.
   */
  public function getEmail(): string {
    return $this->author->getEmail();
  }

  /**
   * Gets the author's IP address.
   * @return string The author's IP address.
   */
  public function getIPAddress(): string {
    return $this->author->getIPAddress();
  }

  /**
   * Gets the author's name.
   * @return string The author's name.
   */
  public function getName(): string {
    return $this->author->getName();
  }

  /**
   * Gets the author's role.
   * @return string The author's role.
   */
  public function getRole(): string {
    return $this->author->getRole();
  }

  /**
   * Gets the URL of the author's website.
   * @return string The URL of the author's website.
   */
  public function getURL(): string {
    return $this->author->getURL();
  }

  /**
   * Gets the author's user agent, that is the string identifying the Web browser used to submit comments.
   * @return string The author's user agent.
   */
  public function getUserAgent(): string {
    return $this->author->getUserAgent();
  }

  /**
   * Converts this object to a map in JSON format.
   * @return \stdClass The map in JSON format corresponding to this object.
   */
  public function jsonSerialize(): \stdClass {
    return $this->author->jsonSerialize();
  }

  /**
   * Sets the author's mail address.
   * @param string $value The new mail address.
   * @return Author This instance.
   */
  public function setEmail(string $value): self {
    $this->author->setEmail($value);
    return $this;
  }

  /**
   * Sets the the author's IP address.
   * @param string $value The new IP address.
   * @return Author This instance.
   */
  public function setIPAddress(string $value): self {
    $this->author->setIPAddress($value);
    return $this;
  }

  /**
   * Sets the author's name.
   * @param string $value The new name.
   * @return Author This instance.
   */
  public function setName(string $value): self {
    $this->author->setName($value);
    return $this;
  }

  /**
   * Sets the author's role. If you set it to `"administrator"`, Akismet will always return `false`.
   * @param string $value The new role.
   * @return Author This instance.
   */
  public function setRole(string $value): self {
    $this->author->setRole($value);
    return $this;
  }

  /**
   * Sets the URL of the author's website.
   * @param string $value The new website URL.
   * @return Author This instance.
   */
  public function setURL(string $value): self {
    $this->author->setURL($value);
    return $this;
  }

  /**
   * Sets the author's user agent, that is the string identifying the Web browser used to submit comments.
   * @param string $value The new user agent.
   * @return Author This instance.
   */
  public function setUserAgent(string $value): self {
    $this->author->setUserAgent($value);
    return $this;
  }
}
