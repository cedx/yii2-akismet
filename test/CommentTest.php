<?php
/**
 * Implementation of the `yii\akismet\test\CommentTest` class.
 */
namespace yii\akismet\test;
use yii\akismet\{Author, Comment};

/**
 * Tests the features of the `yii\akismet\Comment` class.
 */
class CommentTest extends \PHPUnit_Framework_TestCase {

  /**
   * Tests the `Comment` constructor.
   */
  public function testConstructor() {
    $comment = new Comment([
      'content' => 'Hello World!',
      'date' => time(),
      'referrer' => 'https://github.com/cedx/yii2-akismet'
    ]);

    $this->assertEquals('Hello World!', $comment->getContent());
    $this->assertInstanceOf(\DateTime::class, $comment->getDate());
    $this->assertEquals('https://github.com/cedx/yii2-akismet', $comment->getReferrer());
  }

  /**
   * Tests the `Comment::jsonSerialize()` method.
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
}
