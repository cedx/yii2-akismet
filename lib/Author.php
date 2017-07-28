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
