<?php
/**
 * Implementation of the `yii\akismet\test\AuthorTest` class.
 */
namespace yii\akismet\test;
use yii\akismet\{Author};

/**
 * Tests the features of the `yii\akismet\Author` class.
 */
class AuthorTest extends \PHPUnit_Framework_TestCase {

  /**
   * Tests the `Author` constructor.
   */
  public function testConstructor() {
    $author = new Author([
      'email' => 'cedric@belin.io',
      'ipAddress' => '192.168.0.1',
      'name' => 'Cédric Belin'
    ]);

    $this->assertEquals('cedric@belin.io', $author->getEmail());
    $this->assertEquals('192.168.0.1', $author->getIPAddress());
    $this->assertEquals('Cédric Belin', $author->getName());
  }

  /**
   * Tests the `Author::jsonSerialize()` method.
   */
  public function testJsonSerialize() {
    $data = (new Author())->jsonSerialize();
    $this->assertEmpty(get_object_vars($data));

    $data = (new Author([
      'email' => 'cedric@belin.io',
      'ipAddress' => '127.0.0.1',
      'name' => 'Cédric Belin',
      'url' => 'https://belin.io'
    ]))->jsonSerialize();

    $this->assertEquals('Cédric Belin', $data->comment_author);
    $this->assertEquals('cedric@belin.io', $data->comment_author_email);
    $this->assertEquals('https://belin.io', $data->comment_author_url);
    $this->assertEquals('127.0.0.1', $data->user_ip);
  }
}
