<?php
namespace yii\akismet;

use function PHPUnit\Expect\{expect, it};
use PHPUnit\Framework\{TestCase};

/**
 * Tests the features of the `yii\akismet\Blog` class.
 */
class BlogTest extends TestCase {

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
