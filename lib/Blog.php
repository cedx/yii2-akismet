<?php
declare(strict_types=1);
namespace yii\akismet;

use GuzzleHttp\Psr7\{Uri};
use Psr\Http\Message\{UriInterface};
use yii\base\{Model};
use yii\helpers\{Json, StringHelper};

/**
 * Represents the front page or home URL transmitted when making requests.
 * @property \ArrayObject $languages The languages in use on the blog or site, in ISO 639-1 format.
 * @property UriInterface $url The blog or site URL.
 */
class Blog extends Model implements \JsonSerializable {

  /**
   * @var string The character encoding for the values included in comments.
   */
  public $charset = '';

  /**
   * @var \ArrayObject The languages in use on the blog or site, in ISO 639-1 format.
   */
  private $languages;

  /**
   * @var Uri The blog or site URL.
   */
  private $url;

  /**
   * Creates a new blog.
   * @param string|UriInterface $url The blog or site URL.
   * @param array $config Name-value pairs that will be used to initialize the object properties.
   */
  function __construct($url, array $config = []) {
    $this->languages = new \ArrayObject();
    $this->url = is_string($url) ? new Uri($url) : $url;
    parent::__construct($config);
  }

  /**
   * Returns a string representation of this object.
   * @return string The string representation of this object.
   */
  function __toString(): string {
    $json = Json::encode($this);
    return static::class . " $json";
  }

  /**
   * Creates a new blog from the specified JSON map.
   * @param object $map A JSON map representing a blog.
   * @return static The instance corresponding to the specified JSON map, or `null` if a parsing error occurred.
   */
  static function fromJson(object $map): self {
    if (is_array($map)) $map = (object) $map;
    return new static(isset($map->blog) && is_string($map->blog) ? $map->blog : null, [
      'charset' => isset($map->blog_charset) && is_string($map->blog_charset) ? $map->blog_charset : '',
      'languages' => isset($map->blog_lang) && is_string($map->blog_lang) ? StringHelper::explode($map->blog_lang, ',', true, true) : []
    ]);
  }

  /**
   * Gets the languages in use on the blog or site, in ISO 639-1 format.
   * @return \ArrayObject The languages in use on the blog or site.
   */
  function getLanguages(): \ArrayObject {
    return $this->languages;
  }

  /**
   * Gets the blog or site URL.
   * @return UriInterface The blog or site URL.
   */
  function getUrl(): ?UriInterface {
    return $this->url;
  }

  /**
   * Initializes the object.
   */
  function init(): void {
    parent::init();
    if (!mb_strlen($this->charset)) $this->charset = \Yii::$app->charset;
  }

  /**
   * Converts this object to a map in JSON format.
   * @return \stdClass The map in JSON format corresponding to this object.
   */
  function jsonSerialize(): \stdClass {
    $map = new \stdClass;
    $map->blog = (string) $this->getUrl();

    if (mb_strlen($this->charset)) $map->blog_charset = $this->charset;
    if (count($languages = $this->getLanguages())) $map->blog_lang = implode(',', $languages->getArrayCopy());
    return $map;
  }

  /**
   * Returns the validation rules for attributes.
   * @return array[] The validation rules.
   */
  function rules(): array {
    return [
      [['charset', 'url'], 'trim'],
      [['charset'], 'filter', 'filter' => 'mb_strtoupper'],
      [['url'], 'required'],
      [['url'], 'url']
    ];
  }

  /**
   * Sets the languages in use on the blog or site, in ISO 639-1 format.
   * @param string[]|string $values The new languages.
   * @return $this This instance.
   */
  function setLanguages($values): self {
    if (!is_array($values)) $values = is_string($values) ? StringHelper::explode($values, ',', true, true) : [];
    $this->getLanguages()->exchangeArray($values);
    return $this;
  }
}
