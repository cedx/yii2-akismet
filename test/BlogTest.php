<?php
declare(strict_types=1);
namespace yii\akismet;

use function PHPUnit\Expect\{expect, it};
use PHPUnit\Framework\{TestCase};

/**
 * Tests the features of the `yii\akismet\Blog` class.
 */
class BlogTest extends TestCase {

  /**
   * @test Blog::fromJson
   */
  public function testFromJson() {
    it('should return a null reference with a non-object value', function() {
      expect(Blog::fromJson('foo'))->to->be->null;
    });

    it('should return an empty instance with an empty map', function() {
      $blog = Blog::fromJson([]);
      expect($blog->charset)->to->be->empty;
      expect($blog->languages)->to->be->empty;
      expect($blog->url)->to->be->empty;
    });

    it('should return an initialized instance with a non-empty map', function() {
      $blog = Blog::fromJson([
        'blog' => 'https://github.com/cedx/akismet.php',
        'blog_charset' => 'UTF-8',
        'blog_lang' => 'en, fr'
      ]);

      expect($blog->charset)->to->equal('UTF-8');
      expect($blog->languages)->to->equal(['en', 'fr']);
      expect($blog->url)->to->equal('https://github.com/cedx/akismet.php');
    });
  }

  /**
   * @test Blog::jsonSerialize
   */
  public function testJsonSerialize() {
    it('should return an empty map with a newly created instance', function() {
      expect((new Blog)->jsonSerialize())->to->be->empty;
    });

    it('should return a non-empty map with a initialized instance', function() {
      $data = (new Blog([
        'charset' => 'UTF-8',
        'languages' => ['en', 'fr'],
        'url' => 'https://github.com/cedx/yii2-akismet'
      ]))->jsonSerialize();

      expect($data->blog)->to->equal('https://github.com/cedx/yii2-akismet');
      expect($data->blog_charset)->to->equal('UTF-8');
      expect($data->blog_lang)->to->equal('en,fr');
    });
  }

  /**
   * @test Blog::__toString
   */
  public function testToString() {
    $blog = (string) new Blog([
      'charset' => 'UTF-8',
      'languages' => ['en', 'fr'],
      'url' => 'https://github.com/cedx/yii2-akismet'
    ]);

    it('should start with the class name', function() use ($blog) {
      expect($blog)->to->startWith('yii\akismet\Blog {');
    });

    it('should contain the instance properties', function() use ($blog) {
      expect($blog)->to->contain('"blog":"https://github.com/cedx/yii2-akismet"')
        ->and->contain('"blog_charset":"UTF-8"')
        ->and->contain('"blog_lang":"en,fr"');
    });
  }
}
