<?php
namespace yii\akismet;

use yii\base\{Model};
use yii\cedx\validators\{InstanceOfValidator};
use yii\helpers\{Json};

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
      [['author'], InstanceOfValidator::class, 'className' => Author::class],
      [['date', 'postModified'], InstanceOfValidator::class, 'className' => \DateTime::class],
      [['permalink', 'referrer'], 'url', 'defaultScheme' => 'http']
    ];
  }
}
