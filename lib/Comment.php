<?php
declare(strict_types=1);
namespace yii\akismet;

use GuzzleHttp\Psr7\{Uri};
use Psr\Http\Message\{UriInterface};
use yii\base\{Model};
use yii\helpers\{Json};

/**
 * Represents a comment submitted by an author.
 * @property \DateTime $date The UTC timestamp of the creation of the comment.
 * @property UriInterface $permalink The permanent location of the entry the comment is submitted to.
 * @property \DateTime $postModified The UTC timestamp of the publication time for the post, page or thread on which the comment was posted.
 * @property UriInterface $referrer The URL of the webpage that linked to the entry being requested.
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
   * @var string The comment's type. This string value specifies a `CommentType` constant or a made up value like `"registration"`.
   */
  public $type = '';

  /**
   * @var \DateTime The UTC timestamp of the creation of the comment.
   */
  private $date;

  /**
   * @var Uri The permanent location of the entry the comment is submitted to.
   */
  private $permalink;

  /**
   * @var \DateTime The UTC timestamp of the publication time for the post, page or thread on which the comment was posted.
   */
  private $postModified;

  /**
   * @var Uri The URL of the webpage that linked to the entry being requested.
   */
  private $referrer;

  /**
   * Creates a new comment.
   * @param Author $author The comment's author.
   * @param array $config Name-value pairs that will be used to initialize the object properties.
   */
  public function __construct(?Author $author, array $config = []) {
    $this->author = $author;
    parent::__construct($config);
  }

  /**
   * Returns a string representation of this object.
   * @return string The string representation of this object.
   */
  public function __toString(): string {
    $json = Json::encode($this);
    return static::class . " $json";
  }

  /**
   * Creates a new comment from the specified JSON map.
   * @param mixed $map A JSON map representing a comment.
   * @return self The instance corresponding to the specified JSON map, or `null` if a parsing error occurred.
   */
  public static function fromJson($map): ?self {
    if (is_array($map)) $map = (object) $map;
    else if (!is_object($map)) return null;

    $keys = array_keys(\Yii::getObjectVars($map));
    $hasAuthor = count(array_filter($keys, function($key) {
      return preg_match('/^comment_author/', $key) || preg_match('/^user/', $key);
    })) > 0;

    return new static($hasAuthor ? Author::fromJson($map) : null, [
      'content' => isset($map->comment_content) && is_string($map->comment_content) ? $map->comment_content : '',
      'date' => isset($map->comment_date_gmt) && is_string($map->comment_date_gmt) ? $map->comment_date_gmt : null,
      'permalink' => isset($map->permalink) && is_string($map->permalink) ? $map->permalink : null,
      'postModified' => isset($map->comment_post_modified_gmt) && is_string($map->comment_post_modified_gmt) ? $map->comment_post_modified_gmt : null,
      'referrer' => isset($map->referrer) && is_string($map->referrer) ? $map->referrer : null,
      'type' => isset($map->comment_type) && is_string($map->comment_type) ? $map->comment_type : ''
    ]);
  }

  /**
   * Gets the UTC timestamp of the creation of the comment.
   * @return \DateTime The UTC timestamp of the creation of the comment.
   */
  public function getDate(): ?\DateTime {
    return $this->date;
  }

  /**
   * Gets the permanent location of the entry the comment is submitted to.
   * @return UriInterface The permanent location of the entry the comment is submitted to.
   */
  public function getPermalink(): ?UriInterface {
    return $this->permalink;
  }

  /**
   * Gets the UTC timestamp of the publication time for the post, page or thread on which the comment was posted.
   * @return \DateTime The UTC timestamp of the publication time for the post, page or thread on which the comment was posted.
   */
  public function getPostModified(): ?\DateTime {
    return $this->postModified;
  }

  /**
   * Gets the URL of the webpage that linked to the entry being requested.
   * @return UriInterface The URL of the webpage that linked to the entry being requested.
   */
  public function getReferrer(): ?UriInterface {
    return $this->referrer;
  }

  /**
   * Converts this object to a map in JSON format.
   * @return \stdClass The map in JSON format corresponding to this object.
   */
  public function jsonSerialize(): \stdClass {
    $map = $this->author->jsonSerialize();
    if (mb_strlen($this->content)) $map->comment_content = $this->content;
    if ($date = $this->getDate()) $map->comment_date_gmt = $date->format('c');
    if ($postModified = $this->getPostModified()) $map->comment_post_modified_gmt = $postModified->format('c');
    if (mb_strlen($this->type)) $map->comment_type = $this->type;
    if ($permalink = $this->getPermalink()) $map->permalink = (string) $permalink;
    if ($referrer = $this->getReferrer()) $map->referrer = (string) $referrer;
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
      [['permalink', 'referrer'], 'url', 'defaultScheme' => 'http']
    ];
  }

  /**
   * Sets the UTC timestamp of the creation of the comment.
   * @param mixed $value The new UTC timestamp of the creation of the comment.
   * @return self This instance.
   */
  public function setDate($value): self {
    if ($value instanceof \DateTime) $this->date = $value;
    else if (is_string($value)) $this->date = new \DateTime($value);
    else if (is_int($value)) $this->date = new \DateTime("@$value");
    else $this->date = null;

    return $this;
  }

  /**
   * Sets the permanent location of the entry the comment is submitted to.
   * @param string|UriInterface $value The new permanent location of the entry.
   * @return self This instance.
   */
  public function setPermalink($value): self {
    $this->permalink = is_string($value) ? new Uri($value) : $value;
    return $this;
  }

  /**
   * Sets the UTC timestamp of the publication time for the post, page or thread on which the comment was posted.
   * @param mixed $value The new UTC timestamp of the publication time.
   * @return self This instance.
   */
  public function setPostModified($value): self {
    if ($value instanceof \DateTime) $this->postModified = $value;
    else if (is_string($value)) $this->postModified = new \DateTime($value);
    else if (is_int($value)) $this->postModified = new \DateTime("@$value");
    else $this->postModified = null;

    return $this;
  }

  /**
   * Sets the URL of the webpage that linked to the entry being requested.
   * @param string|UriInterface $value The new URL of the webpage that linked to the entry.
   * @return self This instance.
   */
  public function setReferrer($value): self {
    $this->referrer = is_string($value) ? new Uri($value) : $value;
    return $this;
  }
}
