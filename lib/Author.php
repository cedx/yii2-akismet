<?php
declare(strict_types=1);
namespace yii\akismet;

use yii\base\{Model};
use yii\helpers\{Json};

/**
 * Represents the author of a comment.
 */
class Author extends Model implements \JsonSerializable {

  /**
   * @var string The author's mail address.
   */
  public $email = '';

  /**
   * @var string The author's IP address.
   */
  public $ipAddress = '';

  /**
   * @var string The author's name.
   */
  public $name = '';

  /**
   * @var string The author's role.
   */
  public $role = '';

  /**
   * @var string The URL of the author's website.
   */
  public $url = '';

  /**
   * @var string The author's user agent, that is the string identifying the Web browser used to submit comments.
   */
  public $userAgent = '';

  /**
   * Returns a string representation of this object.
   * @return string The string representation of this object.
   */
  public function __toString(): string {
    $json = Json::encode($this);
    return static::class." $json";
  }

  /**
   * Creates a new author from the specified JSON map.
   * @param mixed $map A JSON map representing an author.
   * @return Author The instance corresponding to the specified JSON map, or `null` if a parsing error occurred.
   */
  public static function fromJSON($map) {
    if (is_array($map)) $map = (object) $map;
    return !is_object($map) ? null : \Yii::createObject([
      'class' => static::class,
      'email' => isset($map->comment_author_email) && is_string($map->comment_author_email) ? $map->comment_author_email : '',
      'ipAddress' => isset($map->user_ip) && is_string($map->user_ip) ? $map->user_ip : '',
      'name' => isset($map->comment_author) && is_string($map->comment_author) ? $map->comment_author : '',
      'role' => isset($map->user_role) && is_string($map->user_role) ? $map->user_role : '',
      'url' => isset($map->comment_author_url) && is_string($map->comment_author_url) ? $map->comment_author_url : '',
      'userAgent' => isset($map->user_agent) && is_string($map->user_agent) ? $map->user_agent : ''
    ]);
  }

  /**
   * Converts this object to a map in JSON format.
   * @return \stdClass The map in JSON format corresponding to this object.
   */
  public function jsonSerialize(): \stdClass {
    $map = new \stdClass;
    if (mb_strlen($this->name)) $map->comment_author = $this->name;
    if (mb_strlen($this->email)) $map->comment_author_email = $this->email;
    if (mb_strlen($this->url)) $map->comment_author_url = $this->url;
    if (mb_strlen($this->userAgent)) $map->user_agent = $this->userAgent;
    if (mb_strlen($this->ipAddress)) $map->user_ip = $this->ipAddress;
    if (mb_strlen($this->role)) $map->user_role = $this->role;
    return $map;
  }

  /**
   * Returns the validation rules for attributes.
   * @return array[] The validation rules.
   */
  public function rules(): array {
    return [
      [$this->attributes(), 'trim'],
      [['email'], 'filter', 'filter' => 'mb_strtolower'],
      [['ipAddress', 'userAgent'], 'required'],
      [['email'], 'email', 'checkDNS' => true],
      [['ipAddress'], 'ip']
    ];
  }
}
