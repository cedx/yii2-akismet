<?php
/**
 * Implementation of the `yii\akismet\test\BlogTest` class.
 */
namespace yii\akismet\test;

use PHPUnit\Framework\{TestCase};
use yii\akismet\{Blog};

/**
 * @coversDefaultClass \yii\akismet\Blog
 */
class BlogTest extends TestCase {

  /**
   * @test ::jsonSerialize
   */
  public function testJsonSerialize() {
    $data = (new Blog())->jsonSerialize();
    $this->assertEmpty(get_object_vars($data));

    $data = (new Blog([
      'charset' => 'UTF-8',
      'languages' => 'en, fr',
      'url' => 'https://github.com/cedx/yii2-akismet'
    ]))->jsonSerialize();

    $this->assertEquals('https://github.com/cedx/yii2-akismet', $data->blog);
    $this->assertEquals('UTF-8', $data->blog_charset);
    $this->assertEquals('en,fr', $data->blog_lang);
  }

  /**
   * @test ::__toString
   */
  public function testToString() {
    $blog = (string) new Blog([
      'charset' => 'UTF-8',
      'languages' => 'en, fr',
      'url' => 'https://github.com/cedx/yii2-akismet'
    ]);

    $this->assertStringStartsWith('yii\akismet\Blog {', $blog);
    $this->assertContains('"blog":"https://github.com/cedx/yii2-akismet"', $blog);
    $this->assertContains('"blog_charset":"UTF-8"', $blog);
    $this->assertContains('"blog_lang":"en,fr"', $blog);
  }
}
