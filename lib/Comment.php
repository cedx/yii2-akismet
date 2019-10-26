<?php declare(strict_types=1);
namespace yii\akismet;

use GuzzleHttp\Psr7\{Uri};
use Psr\Http\Message\{UriInterface};
use yii\base\{Model};

/** Represents a comment submitted by an author. */
class Comment extends Model implements \JsonSerializable {

  /** @var Author|null The comment's author. */
  public ?Author $author;

  /** @var string The comment's content. */
  public string $content = '';

  /** @var \DateTime|null The UTC timestamp of the creation of the comment. */
  public ?\DateTime $date = null;

  /** @var UriInterface|null The permanent location of the entry the comment is submitted to. */
  public ?UriInterface $permalink = null;

  /** @var \DateTime|null The UTC timestamp of the publication time for the post, page or thread on which the comment was posted. */
  public ?\DateTime $postModified = null;

  /** @var UriInterface|null The URL of the webpage that linked to the entry being requested. */
  public ?UriInterface $referrer = null;

  /** @var string The comment's type. This string value specifies a `CommentType` constant or a made up value like `"registration"`. */
  public string $type = '';

  /**
   * Creates a new comment.
   * @param Author $author The comment's author.
   * @param array $config Name-value pairs that will be used to initialize the object properties.
   */
  function __construct(?Author $author, array $config = []) {
    $this->author = $author;
    parent::__construct($config);
  }

  /**
   * Creates a new comment from the specified JSON map.
   * @param array $map A JSON map representing a comment.
   * @return self The instance corresponding to the specified JSON map.
   */
  static function fromJson(array $map): self {
    $hasAuthor = count(array_filter(array_keys($map), fn($key) => preg_match('/^comment_author/', $key) || preg_match('/^user/', $key))) > 0;
    return new self($hasAuthor ? Author::fromJson($map) : null, [
      'content' => isset($map['comment_content']) && is_string($map['comment_content']) ? $map['comment_content'] : '',
      'date' => isset($map['comment_date_gmt']) && is_string($map['comment_date_gmt']) ? new \DateTime($map['comment_date_gmt']) : null,
      'permalink' => isset($map['permalink']) && is_string($map['permalink']) ? new Uri($map['permalink']) : null,
      'postModified' => isset($map['comment_post_modified_gmt']) && is_string($map['comment_post_modified_gmt']) ? new \DateTime($map['comment_post_modified_gmt']) : null,
      'referrer' => isset($map['referrer']) && is_string($map['referrer']) ? new Uri($map['referrer']) : null,
      'type' => isset($map['comment_type']) && is_string($map['comment_type']) ? $map['comment_type'] : ''
    ]);
  }

  /**
   * Converts this object to a map in JSON format.
   * @return \stdClass The map in JSON format corresponding to this object.
   */
  function jsonSerialize(): \stdClass {
    $map = $this->author ? $this->author->jsonSerialize() : new \stdClass;
    if (mb_strlen($this->content)) $map->comment_content = $this->content;
    if ($this->date) $map->comment_date_gmt = $this->date->format('c');
    if ($this->postModified) $map->comment_post_modified_gmt = $this->postModified->format('c');
    if (mb_strlen($this->type)) $map->comment_type = $this->type;
    if ($this->permalink) $map->permalink = (string) $this->permalink;
    if ($this->referrer) $map->referrer = (string) $this->referrer;
    return $map;
  }

  /**
   * Returns the validation rules for attributes.
   * @return array[] The validation rules.
   */
  function rules(): array {
    return [
      [['content', 'permalink', 'referrer', 'type'], 'trim'],
      [['author'], 'required'],
      [['permalink', 'referrer'], 'url', 'defaultScheme' => 'http']
    ];
  }
}
