<?php declare(strict_types=1);
namespace yii\akismet;

use function PHPUnit\Expect\{expect, it};
use GuzzleHttp\Psr7\{Uri};
use PHPUnit\Framework\{TestCase};

/** Tests the features of the `yii\akismet\Blog` class. */
class BlogTest extends TestCase {

  /** @test Blog::fromJson() */
  function testFromJson(): void {
    it('should return an empty instance with an empty map', function() {
      $blog = Blog::fromJson(new \stdClass);
      expect($blog->charset)->to->equal('UTF-8');
      expect($blog->languages)->to->be->empty;
      expect($blog->url)->to->be->null;
    });

    it('should return an initialized instance with a non-empty map', function() {
      $blog = Blog::fromJson((object) [
        'blog' => 'https://dev.belin.io/yii2-akismet',
        'blog_charset' => 'ISO-8859-1',
        'blog_lang' => 'en, fr'
      ]);

      expect($blog->charset)->to->equal('ISO-8859-1');
      expect($blog->languages->getArrayCopy())->to->equal(['en', 'fr']);
      expect((string) $blog->url)->to->equal('https://dev.belin.io/yii2-akismet');
    });
  }

  /** @test Blog->jsonSerialize() */
  function testJsonSerialize(): void {
    it('should return only the blog URL with a newly created instance', function() {
      $data = (new Blog(new Uri('https://dev.belin.io/yii2-akismet')))->jsonSerialize();
      expect(\Yii::getObjectVars($data))->to->have->lengthOf(2);
      expect($data->blog)->to->equal('https://dev.belin.io/yii2-akismet');
      expect($data->blog_charset)->to->equal('UTF-8');
    });

    it('should return a non-empty map with a initialized instance', function() {
      $data = (new Blog(new Uri('https://dev.belin.io/yii2-akismet'), [
        'charset' => 'ISO-8859-1',
        'languages' => ['en', 'fr']
      ]))->jsonSerialize();

      expect(\Yii::getObjectVars($data))->to->have->lengthOf(3);
      expect($data->blog)->to->equal('https://dev.belin.io/yii2-akismet');
      expect($data->blog_charset)->to->equal('ISO-8859-1');
      expect($data->blog_lang)->to->equal('en,fr');
    });
  }
}
