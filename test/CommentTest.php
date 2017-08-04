<?php
declare(strict_types=1);
namespace yii\akismet;

use function PHPUnit\Expect\{expect, it};
use PHPUnit\Framework\{TestCase};
use Psr\Http\Message\{UriInterface};
use yii\base\{InvalidConfigException};
use yii\validators\{Validator};

/**
 * Tests the features of the `yii\akismet\Comment` class.
 */
class CommentTest extends TestCase {

  /**
   * @test Comment::fromJson
   */
  public function testFromJson() {
    it('should return a null reference with a non-object value', function() {
      expect(Comment::fromJson('foo'))->to->be->null;
    });

    it('should return an empty instance with an empty map', function() {
      $comment = Comment::fromJson([]);
      expect($comment->author)->to->be->null;
      expect($comment->content)->to->be->empty;
      expect($comment->date)->to->be->null;
      expect($comment->referrer)->to->be->empty;
      expect($comment->type)->to->be->empty;
    });

    it('should return an initialized instance with a non-empty map', function() {
      $comment = Comment::fromJson([
        'comment_author' => 'Cédric Belin',
        'comment_content' => 'A user comment.',
        'comment_date_gmt' => '2000-01-01T00:00:00.000Z',
        'comment_type' => 'trackback',
        'referrer' => 'https://belin.io'
      ]);

      $author = $comment->author;
      expect($author)->to->be->instanceOf(Author::class);
      expect($author->name)->to->equal('Cédric Belin');

      $date = $comment->date;
      expect($date)->to->be->instanceOf(\DateTime::class);
      expect($date->format('Y'))->to->equal(2000);

      expect($comment->content)->to->equal('A user comment.');
      expect($comment->referrer)->to->equal('https://belin.io');
      expect($comment->type)->to->equal(CommentType::TRACKBACK);
    });
  }

  /**
   * @test Comment::isInstanceOf
   */
  public function testIsInstanceOf() {
    it('should throw an exception if the `className` parameter is missing', function() {
      expect(function() { (new Comment)->isInstanceOf('author', [], new Validator); })->to->throw(InvalidConfigException::class);
    });

    it('should throw an exception if the `className` parameter is an unknown class or interface', function() {
      expect(function() { (new Comment)->isInstanceOf('author', ['className' => ''], new Validator); })->to->throw(InvalidConfigException::class);
      expect(function() { (new Comment)->isInstanceOf('author', ['className' => 'Foo\Bar'], new Validator); })->to->throw(InvalidConfigException::class);
    });

    it('should add an error to the validator if the checked attribute is not an instance of the specified class or interface', function() {
      $comment = new Comment(['author' => \Yii::createObject(Blog::class)]);
      $comment->isInstanceOf('author', ['className' => Author::class], new Validator);
      expect($comment->hasErrors())->to->be->true;
    });

    it('should not add any error to the validator if the checked attribute is an instance of the specified class or interface', function() {
      $comment = new Comment(['author' => \Yii::createObject(Author::class)]);
      $comment->isInstanceOf('author', ['className' => Author::class], new Validator);
      expect($comment->hasErrors())->to->be->false;
    });
  }

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
   * @test Comment::setDate
   */
  public function testSetDate() {
    it('should return an instance of `DateTime` for strings and timestamps', function() {
      expect((new Comment(['date' => time()]))->date)->to->be->instanceOf(\DateTime::class);
      expect((new Comment(['date' => '2000-01-01T00:00:00+00:00']))->date)->to->be->instanceOf(\DateTime::class);
    });

    it('should return a `null` reference for unsupported values', function() {
      expect((new Comment(['date' => []]))->date)->to->be->null;
    });
  }

  /**
   * @test Comment::setPermalink
   */
  public function testSetPermalink() {
    it('should return an instance of `UriInterface` for strings', function() {
      $url = (new Comment(['permalink' => 'https://github.com/cedx/yii2-akismet']))->permalink;
      expect($url)->to->be->instanceOf(UriInterface::class);
      expect((string) $url)->to->equal('https://github.com/cedx/yii2-akismet');
    });

    it('should return a `null` reference for unsupported values', function() {
      expect((new Comment(['permalink' => 123]))->permalink)->to->be->null;
    });
  }

  /**
   * @test Comment::setPostModified
   */
  public function testSetPostModified() {
    it('should return an instance of `DateTime` for strings and timestamps', function() {
      expect((new Comment(['postModified' => time()]))->postModified)->to->be->instanceOf(\DateTime::class);
      expect((new Comment(['postModified' => '2000-01-01T00:00:00+00:00']))->postModified)->to->be->instanceOf(\DateTime::class);
    });

    it('should return a `null` reference for unsupported values', function() {
      expect((new Comment(['postModified' => []]))->postModified)->to->be->null;
    });
  }

  /**
   * @test Comment::setReferrer
   */
  public function testSetReferrer() {
    it('should return an instance of `UriInterface` for strings', function() {
      $url = (new Comment(['referrer' => 'https://github.com/cedx/yii2-akismet']))->referrer;
      expect($url)->to->be->instanceOf(UriInterface::class);
      expect((string) $url)->to->equal('https://github.com/cedx/yii2-akismet');
    });

    it('should return a `null` reference for unsupported values', function() {
      expect((new Comment(['referrer' => 123]))->referrer)->to->be->null;
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
