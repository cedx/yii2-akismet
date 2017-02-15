<?php
/**
 * Implementation of the `yii\akismet\Blog` class.
 */
namespace yii\akismet;

use akismet\{Blog as AkismetBlog};
use yii\base\{Object};
use yii\helpers\{Json};

/**
 * Represents the front page or home URL transmitted when making requests.
 */
class Blog extends Object implements \JsonSerializable {

  /**
   * @var AkismetBlog The underlying blog.
   */
  private $blog;

  /**
   * Initializes a new instance of the class.
   * @param array $config Name-value pairs that will be used to initialize the object properties.
   */
  public function __construct(array $config = []) {
    $this->blog = new AkismetBlog();
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
   * Gets the character encoding for the values included in comments.
   * @return string The character encoding for the values included in comments.
   */
  public function getCharset(): string {
    return $this->blog->getCharset();
  }

  /**
   * Gets the languages in use on the blog or site, in ISO 639-1 format, comma-separated.
   * @return \ArrayObject The languages in use on the blog or site.
   */
  public function getLanguages(): \ArrayObject {
    return $this->blog->getLanguages();
  }

  /**
   * Gets the blog or site URL.
   * @return string The blog or site URL.
   */
  public function getURL(): string {
    return $this->blog->getURL();
  }

  /**
   * Converts this object to a map in JSON format.
   * @return \stdClass The map in JSON format corresponding to this object.
   */
  public function jsonSerialize(): \stdClass {
    return $this->blog->jsonSerialize();
  }

  /**
   * Sets the character encoding for the values included in comments.
   * @param string $value The new character encoding.
   * @return Blog This instance.
   */
  public function setCharset(string $value): self {
    $this->blog->setCharset($value);
    return $this;
  }

  /**
   * Sets the languages in use on the blog or site, in ISO 639-1 format, comma-separated.
   * @param array|string $values The new languages.
   * @return Blog This instance.
   */
  public function setLanguages($values): self {
    $this->blog->setLanguages($values);
    return $this;
  }

  /**
   * Sets the blog or site URL.
   * @param string $value The new URL.
   * @return Blog This instance.
   */
  public function setURL(string $value): self {
    $this->blog->setURL($value);
    return $this;
  }
}
