<?php declare(strict_types=1);
namespace yii\akismet;

use function PHPUnit\Expect\{expect, it};
use PHPUnit\Framework\{TestCase};

/** Tests the features of the `yii\akismet\Author` class. */
class AuthorTest extends TestCase {

  /** @test Author::fromJson() */
  function testFromJson(): void {
    it('should return an empty instance with an empty map', function() {
      $author = Author::fromJson(new \stdClass);
      expect($author->email)->to->be->empty;
      expect($author->ipAddress)->to->be->empty;
    });

    it('should return an initialized instance with a non-empty map', function() {
      $author = Author::fromJson((object) [
        'comment_author_email' => 'cedric@belin.io',
        'comment_author_url' => 'https://belin.io'
      ]);

      expect($author->email)->to->equal('cedric@belin.io');
      expect((string) $author->url)->to->equal('https://belin.io');
    });
  }

  /** @test Author->jsonSerialize() */
  function testJsonSerialize(): void {
    it('should return only the IP address and user agent with a newly created instance', function() {
      $data = (new Author('127.0.0.1', 'Doom/6.6.6'))->jsonSerialize();
      expect(\Yii::getObjectVars($data))->to->have->lengthOf(2);
      expect($data->user_agent)->to->equal('Doom/6.6.6');
      expect($data->user_ip)->to->equal('127.0.0.1');
    });

    it('should return a non-empty map with a initialized instance', function() {
      $data = (new Author('192.168.0.1', 'Mozilla/5.0', [
        'email' => 'cedric@belin.io',
        'name' => 'Cédric Belin',
        'url' => 'https://belin.io'
      ]))->jsonSerialize();

      expect(\Yii::getObjectVars($data))->to->have->lengthOf(5);
      expect($data->comment_author)->to->equal('Cédric Belin');
      expect($data->comment_author_email)->to->equal('cedric@belin.io');
      expect($data->comment_author_url)->to->equal('https://belin.io');
      expect($data->user_agent)->to->equal('Mozilla/5.0');
      expect($data->user_ip)->to->equal('192.168.0.1');
    });
  }
}
