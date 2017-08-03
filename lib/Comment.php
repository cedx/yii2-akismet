<?php
declare(strict_types=1);
namespace yii\akismet;

use yii\base\{InvalidConfigException, Model};
use yii\helpers\{Json};
use yii\validators\{Validator};

/**
 * Represents a comment submitted by an author.
 */
class Comment extends Model implements \JsonSerializable {

  /**
   * @var Author The comment's author.
   */
  public $author;

  /**
   * @var string The comment's content.
   */
  public $content = '';

  /**
   * @var \DateTime The UTC timestamp of the creation of the comment.
   */
  public $date;

  /**
   * @var string The permanent location of the entry the comment is submitted to.
   */
  public $permalink = '';

  /**
   * @var \DateTime The UTC timestamp of the publication time for the post, page or thread on which the comment was posted.
   */
  public $postModified;

  /**
   * @var string The URL of the webpage that linked to the entry being requested.
   */
  public $referrer = '';

  /**
   * @var string The comment's type. This string value specifies a `CommentType` constant or a made up value like `"registration"`.
   */
  public $type = '';

  /**
   * Returns a string representation of this object.
   * @return string The string representation of this object.
   */
  public function __toString(): string {
    $json = Json::encode($this);
    return static::class." $json";
  }

  /**
   * Creates a new comment from the specified JSON map.
   * @param mixed $map A JSON map representing a comment.
   * @return Comment The instance corresponding to the specified JSON map, or `null` if a parsing error occurred.
   */
  public static function fromJson($map) {
    if (is_array($map)) $map = (object) $map;
    else if (!is_object($map)) return null;

    $keys = array_keys(\Yii::getObjectVars($map));
    $hasAuthor = count(array_filter($keys, function($key) {
      return preg_match('/^comment_author/', $key) || preg_match('/^user/', $key);
    })) > 0;

    return new static([
      'author' => $hasAuthor ? Author::fromJson($map) : null,
      'content' => isset($map->comment_content) && is_string($map->comment_content) ? $map->comment_content : '',
      'date' => isset($map->comment_date_gmt) && is_string($map->comment_date_gmt) ? new \DateTime($map->comment_date_gmt) : null,
      'permalink' => isset($map->permalink) && is_string($map->permalink) ? $map->permalink : '',
      'postModified' => isset($map->comment_post_modified_gmt) && is_string($map->comment_post_modified_gmt) ? new \DateTime($map->comment_post_modified_gmt) : null,
      'referrer' => isset($map->referrer) && is_string($map->referrer) ? $map->referrer : '',
      'type' => isset($map->comment_type) && is_string($map->comment_type) ? $map->comment_type : ''
    ]);
  }

  /**
   * Checks that a given model attribute is an instance of the specified class.
   * @param string $attribute The name of the attribute to be checked.
   * @param array $params The parameters of the validation rule.
   * @param Validator $validator The validator instance.
   * @throws InvalidConfigException The "className" parameter is empty or invalid.
   */
  public function isInstanceOf(string $attribute, array $params, Validator $validator) {
    if (!isset($params['className']) || !class_exists($params['className']) && !interface_exists($params['className']))
      throw new InvalidConfigException('The "className" parameter is empty or invalid.');

    if (!$this->$attribute instanceof $params['className'])
      $validator->addError($this, $attribute, "The '{attribute}' attribute has not the required type: {$params['className']}");
  }

  /**
   * Converts this object to a map in JSON format.
   * @return \stdClass The map in JSON format corresponding to this object.
   */
  public function jsonSerialize(): \stdClass {
    $map = $this->author ? $this->author->jsonSerialize() : new \stdClass;
    if (mb_strlen($this->content)) $map->comment_content = $this->content;
    if ($this->date) $map->comment_date_gmt = $this->date->format('c');
    if ($this->postModified) $map->comment_post_modified_gmt = $this->postModified->format('c');
    if (mb_strlen($this->type)) $map->comment_type = $this->type;
    if (mb_strlen($this->permalink)) $map->permalink = $this->permalink;
    if (mb_strlen($this->referrer)) $map->referrer = $this->referrer;
    return $map;
  }

  /**
   * Returns the validation rules for attributes.
   * @return array[] The validation rules.
   */
  public function rules(): array {
    return [
      [['content', 'permalink', 'referrer', 'type'], 'trim'],
      [['author'], 'required'],
      [['author'], 'isInstanceOf', 'className' => Author::class],
      [['date', 'postModified'], 'isInstanceOf', 'className' => \DateTime::class],
      [['permalink', 'referrer'], 'url', 'defaultScheme' => 'http']
    ];
  }
}
