<?php
namespace yii\akismet;

use function PHPUnit\Expect\{expect, it};
use PHPUnit\Framework\{TestCase};

/**
 * Tests the features of the `yii\akismet\Author` class.
 */
class AuthorTest extends TestCase {

  /**
   * @test Author::jsonSerialize
   */
  public function testJsonSerialize() {
    it('should return an empty map with a newly created instance', function() {
      expect((new Author)->jsonSerialize())->to->be->empty;
    });

    it('should return a non-empty map with a initialized instance', function() {
      $data = (new Author([
        'email' => 'cedric@belin.io',
        'ipAddress' => '127.0.0.1',
        'name' => 'Cédric Belin',
        'url' => 'https://belin.io'
      ]))->jsonSerialize();

      expect($data->comment_author)->to->equal('Cédric Belin');
      expect($data->comment_author_email)->to->equal('cedric@belin.io');
      expect($data->comment_author_url)->to->equal('https://belin.io');
      expect($data->user_ip)->to->equal('127.0.0.1');
    });
  }

  /**
   * @test Author::__toString
   */
  public function testToString() {
    $author = (string) new Author([
      'email' => 'cedric@belin.io',
      'ipAddress' => '127.0.0.1',
      'name' => 'Cédric Belin',
      'url' => 'https://belin.io'
    ]);

    it('should start with the class name', function() use ($author) {
      expect($author)->to->startWith('yii\akismet\Author {');
    });

    it('should contain the instance properties', function() use ($author) {
      expect($author)->to->contain('"comment_author":"Cédric Belin"')
        ->and->contain('"comment_author_email":"cedric@belin.io"')
        ->and->contain('"comment_author_url":"https://belin.io"')
        ->and->contain('"user_ip":"127.0.0.1"');
    });
  }
}
