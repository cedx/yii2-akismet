<?php declare(strict_types=1);
namespace yii\akismet;

use PHPUnit\Framework\{TestCase};

/**
 * Tests the features of the `yii\akismet\Author` class.
 */
class AuthorTest extends TestCase {

  /**
   * Tests the `Author::fromJson()` method.
   * @test
   */
  function testFromJson(): void {
    // It should return an empty instance with an empty map.
    $author = Author::fromJson(new \stdClass);
    assertThat($author->email, isEmpty());
    assertThat($author->ipAddress, isEmpty());

    // It should return an initialized instance with a non-empty map.
    $author = Author::fromJson((object) [
      'comment_author_email' => 'cedric@belin.io',
      'comment_author_url' => 'https://belin.io'
    ]);

    assertThat($author->email, equalTo('cedric@belin.io'));
    assertThat((string) $author->url, equalTo('https://belin.io'));
  }

  /**
   * Tests the `Author::jsonSerialize()` method.
   * @test
   */
  function testJsonSerialize(): void {
    // It should return only the IP address and user agent with a newly created instance.
    $data = (new Author('127.0.0.1', 'Doom/6.6.6'))->jsonSerialize();
    assertThat(\Yii::getObjectVars($data), countOf(2));
    assertThat($data->user_agent, equalTo('Doom/6.6.6'));
    assertThat($data->user_ip, equalTo('127.0.0.1'));

    // It should return a non-empty map with a initialized instance.
    $data = (new Author('192.168.0.1', 'Mozilla/5.0', [
      'email' => 'cedric@belin.io',
      'name' => 'Cédric Belin',
      'url' => 'https://belin.io'
    ]))->jsonSerialize();

    assertThat(\Yii::getObjectVars($data), countOf(5));
    assertThat($data->comment_author, equalTo('Cédric Belin'));
    assertThat($data->comment_author_email, equalTo('cedric@belin.io'));
    assertThat($data->comment_author_url, equalTo('https://belin.io'));
    assertThat($data->user_agent, equalTo('Mozilla/5.0'));
    assertThat($data->user_ip, equalTo('192.168.0.1'));
  }
}
