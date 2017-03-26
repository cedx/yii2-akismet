<?php
namespace yii\akismet;

use yii\base\{Model};
use yii\helpers\{Json};

/**
 * Represents a comment submitted by an author.
 * @property Author $author The comment's author.
 * @property string $content The comment's content.
 * @property \DateTime $date The UTC timestamp of the creation of the comment.
 * @property string $permalink The permanent location of the entry the comment is submitted to.
 * @property \DateTime $postModified The UTC timestamp of the publication time for the post, page or thread on which the comment was posted.
 * @property string $referrer The URL of the webpage that linked to the entry being requested.
 * @property string $type The comment's type.
 */
class Comment extends Model implements \JsonSerializable {

  /**
   * @var Author The comment's author.
   */
  private $author;

  /**
   * @var AkismetComment The underlying comment.
   */
  private $comment;

  /**
   * Initializes a new instance of the class.
   * @param array $config Name-value pairs that will be used to initialize the object properties.
   */
  public function __construct(array $config = []) {
    $this->comment = new AkismetComment();
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
   * Gets the comment's author.
   * @return Author The comment's author.
   */
  public function getAuthor() {
    return $this->author;
  }

  /**
   * Gets the comment's content.
   * @return string The comment's content.
   */
  public function getContent(): string {
    return $this->comment->getContent();
  }

  /**
   * Gets the UTC timestamp of the creation of the comment.
   * @return \DateTime The UTC timestamp of the creation of the comment.
   */
  public function getDate() {
    return $this->comment->getDate();
  }

  /**
   * Gets the permanent location of the entry the comment is submitted to.
   * @return string The permanent location of the entry the comment is submitted to.
   */
  public function getPermalink(): string {
    return $this->comment->getPermalink();
  }

  /**
   * Gets the UTC timestamp of the publication time for the post, page or thread on which the comment was posted.
   * @return \DateTime The UTC timestamp of the publication time for the post, page or thread on which the comment was posted.
   */
  public function getPostModified() {
    return $this->comment->getPostModified();
  }

  /**
   * Gets the URL of the webpage that linked to the entry being requested.
   * @return string The URL of the webpage that linked to the entry being requested.
   */
  public function getReferrer(): string {
    return $this->comment->getReferrer();
  }

  /**
   * Gets the comment's type. This string value specifies a made up value like `"comment"`, `"pingback"` or `"trackback"`.
   * @return string The comment's type. This string value specifies a made up value like `"comment"`, `"pingback"` or `"trackback"`.
   */
  public function getType(): string {
    return $this->comment->getType();
  }

  /**
   * Converts this object to a map in JSON format.
   * @return \stdClass The map in JSON format corresponding to this object.
   */
  public function jsonSerialize(): \stdClass {
    $map = $this->comment->jsonSerialize();
    if ($author = $this->getAuthor()) {
      foreach (get_object_vars($author->jsonSerialize()) as $key => $value) $map->$key = $value;
    }

    return $map;
  }

  /**
   * Sets the comment's author.
   * @param Author $value The new author.
   * @return Comment This instance.
   */
  public function setAuthor(Author $value = null): self {
    $this->author = $value;
    $this->comment->setAuthor(AkismetAuthor::fromJSON($this->author ? $this->author->jsonSerialize() : null));
    return $this;
  }

  /**
   * Sets the comment's content.
   * @param string $value The new content.
   * @return Comment This instance.
   */
  public function setContent(string $value): self {
    $this->comment->setContent($value);
    return $this;
  }

  /**
   * Sets the UTC timestamp of the creation of the comment.
   * @param mixed $value The new UTC timestamp of the creation of the comment.
   * @return Comment This instance.
   */
  public function setDate($value): self {
    $this->comment->setDate($value);
    return $this;
  }

  /**
   * Sets the permanent location of the entry the comment is submitted to.
   * @param string $value The new permanent location of the entry.
   * @return Comment This instance.
   */
  public function setPermalink(string $value): self {
    $this->comment->setPermalink($value);
    return $this;
  }

  /**
   * Sets the UTC timestamp of the publication time for the post, page or thread on which the comment was posted.
   * @param mixed $value The new UTC timestamp of the publication time.
   * @return Comment This instance.
   */
  public function setPostModified($value): self {
    $this->comment->setPostModified($value);
    return $this;
  }

  /**
   * Sets the URL of the webpage that linked to the entry being requested.
   * @param string $value The new URL of the webpage that linked to the entry.
   * @return Comment This instance.
   */
  public function setReferrer(string $value): self {
    $this->comment->setReferrer($value);
    return $this;
  }

  /**
   * Sets the comment's type.
   * @param string $value The new type.
   * @return Comment This instance.
   */
  public function setType(string $value): self {
    $this->comment->setType($value);
    return $this;
  }
}
