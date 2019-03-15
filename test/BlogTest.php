<?php
declare(strict_types=1);
namespace yii\akismet;

use function League\Uri\{create as createUri};
use PHPUnit\Framework\{TestCase};

/**
 * Tests the features of the `yii\akismet\Blog` class.
 */
class BlogTest extends TestCase {

  /**
   * Tests the `Blog::fromJson()` method.
   * @test
   */
  function testFromJson(): void {
    // It should return an empty instance with an empty map.
    $blog = Blog::fromJson(new \stdClass);
    assertThat($blog->charset, equalTo('UTF-8'));
    assertThat($blog->languages, isEmpty());
    assertThat($blog->url, isNull());

    // It should return an initialized instance with a non-empty map.
    $blog = Blog::fromJson((object) [
      'blog' => 'https://dev.belin.io/yii2-akismet',
      'blog_charset' => 'ISO-8859-1',
      'blog_lang' => 'en, fr'
    ]);

    assertThat($blog->charset, equalTo('ISO-8859-1'));
    assertThat($blog->languages->getArrayCopy(), equalTo(['en', 'fr']));
    assertThat((string) $blog->url, equalTo('https://dev.belin.io/yii2-akismet'));
  }

  /**
   * Tests the `Blog::jsonSerialize()` method.
   * @test
   */
  function testJsonSerialize(): void {
    // It should return only the blog URL with a newly created instance.
    $data = (new Blog(createUri('https://dev.belin.io/yii2-akismet')))->jsonSerialize();
    assertThat(\Yii::getObjectVars($data), countOf(2));
    assertThat($data->blog, equalTo('https://dev.belin.io/yii2-akismet'));
    assertThat($data->blog_charset, equalTo('UTF-8'));

    // It should return a non-empty map with a initialized instance.
    $data = (new Blog(createUri('https://dev.belin.io/yii2-akismet'), [
      'charset' => 'ISO-8859-1',
      'languages' => ['en', 'fr']
    ]))->jsonSerialize();

    assertThat(\Yii::getObjectVars($data), countOf(3));
    assertThat($data->blog, equalTo('https://dev.belin.io/yii2-akismet'));
    assertThat($data->blog_charset, equalTo('ISO-8859-1'));
    assertThat($data->blog_lang, equalTo('en,fr'));
  }

  /**
   * Tests the `Blog::__toString()` method.
   * @test
   */
  function testToString(): void {
    $blog = (string) new Blog(createUri('https://dev.belin.io/yii2-akismet'), [
      'charset' => 'UTF-8',
      'languages' => ['en', 'fr']
    ]);

    // It should start with the class name.
    assertThat($blog, stringStartsWith('yii\akismet\Blog {'));

    // It should contain the instance properties.
    assertThat($blog, logicalAnd(
      contains('"blog":"https://dev.belin.io/yii2-akismet"'),
      contains('"blog_charset":"UTF-8"'),
      contains('"blog_lang":"en,fr"')
    ));
  }
}
