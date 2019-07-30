<?php declare(strict_types=1);
namespace yii\akismet;

use GuzzleHttp\Psr7\{Uri};
use yii\base\{Model};

/** Represents the author of a comment. */
class Author extends Model implements \JsonSerializable {

  /** @var string The author's mail address. */
  public $email = '';

  /** @var string The author's IP address. */
  public $ipAddress;

  /** @var string The author's name. If you set it to `"viagra-test-123"`, Akismet will always return `true`. */
  public $name = '';

  /** @var string The author's role. If you set it to `"administrator"`, Akismet will always return `false`. */
  public $role = '';

  /** @var \Psr\Http\Message\UriInterface|null The URL of the author's website. */
  public $url;

  /** @var string The author's user agent, that is the string identifying the Web browser used to submit comments. */
  public $userAgent;

  /**
   * Creates a new author.
   * @param string $ipAddress The author's IP address.
   * @param string $userAgent The author's user agent.
   * @param array $config Name-value pairs that will be used to initialize the object properties.
   */
  function __construct(string $ipAddress, string $userAgent, array $config = []) {
    $this->ipAddress = $ipAddress;
    $this->userAgent = $userAgent;
    parent::__construct($config);
  }

  /**
   * Creates a new author from the specified JSON map.
   * @param array $map A JSON map representing an author.
   * @return static The instance corresponding to the specified JSON map.
   */
  static function fromJson(array $map): self {
    $options = [
      'email' => isset($map['comment_author_email']) && is_string($map['comment_author_email']) ? $map['comment_author_email'] : '',
      'name' => isset($map['comment_author']) && is_string($map['comment_author']) ? $map['comment_author'] : '',
      'role' => isset($map['user_role']) && is_string($map['user_role']) ? $map['user_role'] : '',
      'url' => isset($map['comment_author_url']) && is_string($map['comment_author_url']) ? new Uri($map['comment_author_url']) : null
    ];

    return new self(
      isset($map['user_ip']) && is_string($map['user_ip']) ? $map['user_ip'] : '',
      isset($map['user_agent']) && is_string($map['user_agent']) ? $map['user_agent'] : '',
      $options
    );
  }

  /**
   * Converts this object to a map in JSON format.
   * @return \stdClass The map in JSON format corresponding to this object.
   */
  function jsonSerialize(): \stdClass {
    $map = new \stdClass;
    $map->user_agent = $this->userAgent;
    $map->user_ip = $this->ipAddress;

    if (mb_strlen($this->name)) $map->comment_author = $this->name;
    if (mb_strlen($this->email)) $map->comment_author_email = $this->email;
    if ($this->url) $map->comment_author_url = (string) $this->url;
    if (mb_strlen($this->role)) $map->user_role = $this->role;
    return $map;
  }

  /**
   * Returns the validation rules for attributes.
   * @return array[] The validation rules.
   */
  function rules(): array {
    return [
      [$this->attributes(), 'trim'],
      [['email'], 'filter', 'filter' => 'mb_strtolower'],
      [['ipAddress', 'userAgent'], 'required'],
      [['email'], 'email', 'checkDNS' => true],
      [['ipAddress'], 'ip']
    ];
  }
}
