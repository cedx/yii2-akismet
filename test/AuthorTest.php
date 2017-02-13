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
