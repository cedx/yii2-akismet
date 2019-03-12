<?php
declare(strict_types=1);
namespace yii\akismet;

use PHPUnit\Framework\{TestCase};

/**
 * Tests the features of the `yii\akismet\Comment` class.
 */
class CommentTest extends TestCase {

  /**
   * Tests the `Comment::fromJson()` method.
   * @test
   */
  function testFromJson(): void {
    // It should return an empty instance with an empty map.
    $comment = Comment::fromJson(new \stdClass);
    assertThat($comment->author, isNull());
    assertThat($comment->content, isEmpty());
    assertThat($comment->date, isNull());
    assertThat($comment->referrer, isEmpty());
    assertThat($comment->type, isEmpty());

    // It should return an initialized instance with a non-empty map.
    $comment = Comment::fromJson((object) [
      'comment_author' => 'Cédric Belin',
      'comment_content' => 'A user comment.',
      'comment_date_gmt' => '2000-01-01T00:00:00.000Z',
      'comment_type' => 'trackback',
      'referrer' => 'https://belin.io'
    ]);

    assertThat($comment->author, isInstanceOf(Author::class));
    assertThat($comment->author->name, equalTo('Cédric Belin'));

    assertThat($comment->date, isInstanceOf(\DateTime::class));
    assertThat($comment->date->format('Y'), equalTo(2000));

    assertThat($comment->content, equalTo('A user comment.'));
    assertThat($comment->referrer, equalTo('https://belin.io'));
    assertThat($comment->type, equalTo(CommentType::TRACKBACK));
  }

  /**
   * Tests the `Comment::jsonSerialize()` method.
   * @test
   */
  function testJsonSerialize(): void {
    // It should return only the author info with a newly created instance.
    $data = (new Comment(new Author('127.0.0.1', 'Doom/6.6.6')))->jsonSerialize();
    assertThat(\Yii::getObjectVars($data), countOf(2));
    assertThat($data->user_agent, equalTo('Doom/6.6.6'));
    assertThat($data->user_ip, equalTo('127.0.0.1'));

    // It should return a non-empty map with a initialized instance.
    $data = (new Comment(new Author('127.0.0.1', 'Doom/6.6.6', ['name' => 'Cédric Belin']), [
      'content' => 'A user comment.',
      'date' => '2000-01-01T00:00:00.000Z',
      'referrer' => 'https://belin.io',
      'type' => CommentType::PINGBACK
    ]))->jsonSerialize();

    assertThat(\Yii::getObjectVars($data), countOf(7));
    assertThat($data->comment_author, equalTo('Cédric Belin'));
    assertThat($data->comment_content, equalTo('A user comment.'));
    assertThat($data->comment_date_gmt, equalTo('2000-01-01T00:00:00+00:00'));
    assertThat($data->comment_type, equalTo('pingback'));
    assertThat($data->referrer, equalTo('https://belin.io'));
    assertThat($data->user_agent, equalTo('Doom/6.6.6'));
    assertThat($data->user_ip, equalTo('127.0.0.1'));
  }

  /**
   * Tests the `Comment::__toString()` method.
   * @test
   */
  function testToString(): void {
    $comment = (string) new Comment(new Author('127.0.0.1', 'Doom/6.6.6', ['name' => 'Cédric Belin']), [
      'content' => 'A user comment.',
      'date' => '2000-01-01T00:00:00.000Z',
      'referrer' => 'https://belin.io',
      'type' => CommentType::PINGBACK
    ]);

    // It should start with the class name.
    assertThat($comment, stringStartsWith('yii\akismet\Comment {');

    // It should contain the instance properties.
    assertThat($comment)->to->contain('"comment_author":"Cédric Belin"')
      ->and->contain('"comment_content":"A user comment."')
      ->and->contain('"comment_type":"pingback"')
      ->and->contain('"comment_date_gmt":"2000-01-01T00:00:00+00:00"')
      ->and->contain('"referrer":"https://belin.io"')
      ->and->contain('"user_agent":"Doom/6.6.6"')
      ->and->contain('"user_ip":"127.0.0.1"');
  }
}
