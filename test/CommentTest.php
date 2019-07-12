<?php declare(strict_types=1);
namespace yii\akismet;

use function PHPUnit\Expect\{expect, it};
use PHPUnit\Framework\{TestCase};

/** Tests the features of the `yii\akismet\Comment` class. */
class CommentTest extends TestCase {

  /** @test Comment::fromJson() */
  function testFromJson(): void {
    it('should return an empty instance with an empty map', function() {
      $comment = Comment::fromJson(new \stdClass);
      expect($comment->author)->to->be->null;
      expect($comment->content)->to->be->empty;
      expect($comment->date)->to->be->null;
      expect($comment->referrer)->to->be->empty;
      expect($comment->type)->to->be->empty;
    });

    it('should return an initialized instance with a non-empty map', function() {
      $comment = Comment::fromJson((object) [
        'comment_author' => 'Cédric Belin',
        'comment_content' => 'A user comment.',
        'comment_date_gmt' => '2000-01-01T00:00:00.000Z',
        'comment_type' => 'trackback',
        'referrer' => 'https://belin.io'
      ]);

      /** @var Author $author */
      $author = $comment->author;
      expect($author->name)->to->equal('Cédric Belin');

      /** @var \DateTime $date */
      $date = $comment->date;
      expect($date->format('Y'))->to->equal(2000);

      expect($comment->content)->to->equal('A user comment.');
      expect($comment->referrer)->to->equal('https://belin.io');
      expect($comment->type)->to->equal(CommentType::trackback);
    });
  }

  /** @test Comment->jsonSerialize() */
  function testJsonSerialize(): void {
    it('should return only the author info with a newly created instance', function() {
      $data = (new Comment(new Author('127.0.0.1', 'Doom/6.6.6')))->jsonSerialize();
      expect(\Yii::getObjectVars($data))->to->have->lengthOf(2);
      expect($data->user_agent)->to->equal('Doom/6.6.6');
      expect($data->user_ip)->to->equal('127.0.0.1');
    });

    it('should return a non-empty map with a initialized instance', function() {
      $data = (new Comment(new Author('127.0.0.1', 'Doom/6.6.6', ['name' => 'Cédric Belin']), [
        'content' => 'A user comment.',
        'date' => new \DateTime('2000-01-01T00:00:00.000Z'),
        'referrer' => 'https://belin.io',
        'type' => CommentType::pingback
      ]))->jsonSerialize();

      expect(\Yii::getObjectVars($data))->to->have->lengthOf(7);
      expect($data->comment_author)->to->equal('Cédric Belin');
      expect($data->comment_content)->to->equal('A user comment.');
      expect($data->comment_date_gmt)->to->equal('2000-01-01T00:00:00+00:00');
      expect($data->comment_type)->to->equal('pingback');
      expect($data->referrer)->to->equal('https://belin.io');
      expect($data->user_agent)->to->equal('Doom/6.6.6');
      expect($data->user_ip)->to->equal('127.0.0.1');
    });
  }
}
