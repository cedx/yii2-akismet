<?php
declare(strict_types=1);
namespace yii\akismet;

use GuzzleHttp\Psr7\{Uri};
use Psr\Http\Message\{UriInterface};
use yii\base\{Model};
use yii\helpers\{Json};

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
   * Initializes a new instance of the class.
   * @param array $config Name-value pairs that will be used to initialize the object properties.
   */
  public function __construct(array $config = []) {
    $this->languages = new \ArrayObject();
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
   * Creates a new blog from the specified JSON map.
   * @param mixed $map A JSON map representing a blog.
   * @return Blog The instance corresponding to the specified JSON map, or `null` if a parsing error occurred.
   */
  public static function fromJson($map) {
    if (is_array($map)) $map = (object) $map;
    return !is_object($map) ? null : new static([
      'charset' => isset($map->blog_charset) && is_string($map->blog_charset) ? $map->blog_charset : '',
      'languages' => isset($map->blog_lang) && is_string($map->blog_lang) ? $map->blog_lang : [],
      'url' => isset($map->blog) && is_string($map->blog) ? $map->blog : null
    ]);
  }

  /**
   * Gets the languages in use on the blog or site, in ISO 639-1 format.
   * @return \ArrayObject The languages in use on the blog or site.
   */
  public function getLanguages(): \ArrayObject {
    return $this->languages;
  }

  /**
   * Gets the blog or site URL.
   * @return UriInterface The blog or site URL.
   */
  public function getUrl() {
    return $this->url;
  }

  /**
   * Converts this object to a map in JSON format.
   * @return \stdClass The map in JSON format corresponding to this object.
   */
  public function jsonSerialize(): \stdClass {
    $map = new \stdClass;
    if ($url = $this->getUrl()) $map->blog = (string) $url;
    if (mb_strlen($this->charset)) $map->blog_charset = $this->charset;
    if (count($languages = $this->getLanguages())) $map->blog_lang = implode(',', $languages->getArrayCopy());
    return $map;
  }

  /**
   * Returns the validation rules for attributes.
   * @return array[] The validation rules.
   */
  public function rules(): array {
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
   * @return Blog This instance.
   */
  public function setLanguages($values): self {
    if (!is_array($values)) $values = is_string($values) ? explode(',', $values) : [];
    $this->getLanguages()->exchangeArray(array_values(array_filter(array_map('trim', $values), function($value) {
      return mb_strlen($value) > 0;
    })));

    return $this;
  }

  /**
   * Sets the blog or site URL.
   * @param string|UriInterface $value The new URL.
   * @return Blog This instance.
   */
  public function setUrl($value): self {
    if ($value instanceof UriInterface) $this->url = $value;
    else if (is_string($value)) $this->url = new Uri($value);
    else $this->url = null;

    return $this;
  }
}
