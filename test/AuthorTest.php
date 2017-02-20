<?php
/**
 * Implementation of the `yii\akismet\test\AuthorTest` class.
 */
namespace yii\akismet\test;

use PHPUnit\Framework\{TestCase};
use yii\akismet\{Author};

/**
 * @coversDefaultClass \yii\akismet\Author
 */
class AuthorTest extends TestCase {

  /**
   * @test ::jsonSerialize
   */
  public function testJsonSerialize() {
    // Should return an empty map with a newly created instance.
    $data = (new Author())->jsonSerialize();
    $this->assertEmpty(get_object_vars($data));

    // Should return a non-empty map with a initialized instance.
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

  /**
   * @test ::__toString
   */
  public function testToString() {
    $author = (string) new Author([
      'email' => 'cedric@belin.io',
      'ipAddress' => '127.0.0.1',
      'name' => 'Cédric Belin',
      'url' => 'https://belin.io'
    ]);

    // Should start with the class name.
    $this->assertStringStartsWith('yii\akismet\Author {', $author);

    // Should contain the instance properties.
    $this->assertContains('"comment_author":"Cédric Belin"', $author);
    $this->assertContains('"comment_author_email":"cedric@belin.io"', $author);
    $this->assertContains('"comment_author_url":"https://belin.io"', $author);
    $this->assertContains('"user_ip":"127.0.0.1"', $author);
  }
}
