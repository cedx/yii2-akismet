<?php
declare(strict_types=1);
namespace yii\akismet;

use yii\base\{Model};
use yii\helpers\{Json};

/**
 * Represents the front page or home URL transmitted when making requests.
 */
class Blog extends Model implements \JsonSerializable {

  /**
   * @var string The character encoding for the values included in comments.
   */
  public $charset = '';

  /**
   * @var string[] The languages in use on the blog or site, in ISO 639-1 format.
   */
  public $languages = [];

  /**
   * @var string The blog or site URL.
   */
  public $url = '';

  /**
   * Returns a string representation of this object.
   * @return string The string representation of this object.
   */
  public function __toString(): string {
    $json = Json::encode($this);
    return static::class." $json";
  }

  /**
   * Converts this object to a map in JSON format.
   * @return \stdClass The map in JSON format corresponding to this object.
   */
  public function jsonSerialize(): \stdClass {
    $map = new \stdClass;
    if (mb_strlen($this->url)) $map->blog = $this->url;
    if (mb_strlen($this->charset)) $map->blog_charset = $this->charset;
    if (count($this->languages)) $map->blog_lang = implode(',', $this->languages);
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
}
