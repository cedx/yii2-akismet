<?php
/**
 * Implementation of the `yii\akismet\test\BlogTest` class.
 */
namespace yii\akismet\test;
use yii\akismet\{Blog};

/**
 * Tests the features of the `yii\akismet\Blog` class.
 */
class BlogTest extends \PHPUnit_Framework_TestCase {

  /**
   * Tests the `Blog` constructor.
   */
  public function testConstructor() {
    $blog = new Blog([
      'charset' => 'UTF-8',
      'language' => 'en',
      'url' => 'https://github.com/cedx/yii2-akismet'
    ]);

    $this->assertEquals('UTF-8', $blog->getCharset());
    $this->assertEquals('en', $blog->getLanguage());
    $this->assertEquals('https://github.com/cedx/yii2-akismet', $blog->getURL());
  }

  /**
   * Tests the `Blog::jsonSerialize()` method.
   */
  public function testJsonSerialize() {
    $data = (new Blog())->jsonSerialize();
    $this->assertEquals(0, count((array) $data));

    $data = (new Blog([
      'charset' => 'UTF-8',
      'language' => 'en',
      'url' => 'https://github.com/cedx/yii2-akismet'
    ]))->jsonSerialize();

    $this->assertEquals('https://github.com/cedx/yii2-akismet', $data->blog);
    $this->assertEquals('UTF-8', $data->blog_charset);
    $this->assertEquals('en', $data->blog_lang);
  }
}
