<?php
namespace yii\akismet;
use PHPUnit\Framework\{TestCase};

/**
 * Tests the features of the `yii\akismet\Comment` class.
 */
class CommentTest extends TestCase {

  /**
   * @test Comment::jsonSerialize
   */
  public function testJsonSerialize() {
    it('should return an empty map with a newly created instance', function() {
      expect((new Comment)->jsonSerialize())->to->be->empty;
    });

    it('should return a non-empty map with a initialized instance', function() {
      $data = (new Comment([
        'author' => \Yii::createObject(['class' => Author::class, 'name' => 'Cédric Belin']),
        'content' => 'A user comment.',
        'referrer' => 'https://belin.io',
        'type' => CommentType::PINGBACK
      ]))->jsonSerialize();

      expect($data->comment_author)->to->equal('Cédric Belin');
      expect($data->comment_content)->to->equal('A user comment.');
      expect($data->comment_type)->to->equal(CommentType::PINGBACK);
      expect($data->referrer)->to->equal('https://belin.io');
    });
  }

  /**
   * @test Comment::__toString
   */
  public function testToString() {
    $comment = (string) new Comment([
      'author' => \Yii::createObject(['class' => Author::class, 'name' => 'Cédric Belin']),
      'content' => 'A user comment.',
      'referrer' => 'https://belin.io',
      'type' => CommentType::PINGBACK
    ]);

    it('should start with the class name', function() use ($comment) {
      expect($comment)->to->startWith('yii\akismet\Comment {');
    });

    it('should contain the instance properties', function() use ($comment) {
      expect($comment)->to->contain('"comment_author":"Cédric Belin"')
        ->and->contain('"comment_content":"A user comment."')
        ->and->contain('"comment_type":"pingback"')
        ->and->contain('"referrer":"https://belin.io"');
    });
  }
}
