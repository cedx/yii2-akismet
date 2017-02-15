<?php
/**
 * Implementation of the `yii\akismet\test\CommentTest` class.
 */
namespace yii\akismet\test;

use PHPUnit\Framework\{TestCase};
use yii\akismet\{Author, Comment};

/**
 * @coversDefaultClass \yii\akismet\Comment
 */
class CommentTest extends TestCase {

  /**
   * @test ::jsonSerialize
   */
  public function testJsonSerialize() {
    $data = (new Comment())->jsonSerialize();
    $this->assertEmpty(get_object_vars($data));

    $data = (new Comment([
      'author' => \Yii::createObject(['class' => Author::class, 'name' => 'Cédric Belin']),
      'content' => 'A user comment.',
      'referrer' => 'https://belin.io',
      'type' => 'pingback'
    ]))->jsonSerialize();

    $this->assertEquals('Cédric Belin', $data->comment_author);
    $this->assertEquals('A user comment.', $data->comment_content);
    $this->assertEquals('pingback', $data->comment_type);
    $this->assertEquals('https://belin.io', $data->referrer);
  }

  /**
   * @test ::__toString
   */
  public function testToString() {
    $comment = (string) new Comment([
      'author' => \Yii::createObject(['class' => Author::class, 'name' => 'Cédric Belin']),
      'content' => 'A user comment.',
      'referrer' => 'https://belin.io',
      'type' => 'pingback'
    ]);

    $this->assertStringStartsWith('yii\akismet\Comment {', $comment);
    $this->assertContains('"comment_author":"Cédric Belin"', $comment);
    $this->assertContains('"comment_content":"A user comment."', $comment);
    $this->assertContains('"comment_type":"pingback"', $comment);
    $this->assertContains('"referrer":"https://belin.io"', $comment);
  }
}
