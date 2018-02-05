# Testing
When you will integrate the library with your own application, you will of course need to test it. Often we see developers get ahead of themselves, making a few trivial API calls with minimal values and drawing the wrong conclusions about Akismet's accuracy.

## Simulate a positive (spam) result
Make a [comment check](../features/comment_check.md) API call with the `Author->name` set to `"viagra-test-123"` or `Author->email` set to `"akismet-guaranteed-spam@example.com"`. Populate all other required fields with typical values.

The Akismet API will always return a `true` response to a valid request with one of those values. If you receive anything else, something is wrong in your client, data, or communications.

```php
<?php
use yii\akismet\{Author, Client, Comment};

$client = new Client([
  'apiKey' => '123YourAPIKey',
  'blog' => 'http://www.yourblog.com'
]);

$author = new Author('127.0.0.1', 'Mozilla/5.0', ['name' => 'viagra-test-123']);
$comment = new Comment($author, 'A user comment');

$isSpam = $client->checkComment($comment);
print("It should be 'true': $isSpam");
```

## Simulate a negative (not spam) result
Make a [comment check](../features/comment_check.md) API call with the `Author->role` set to `"administrator"` and all other required fields populated with typical values.

The Akismet API will always return a `false` response. Any other response indicates a data or communication problem.

```php
<?php
use yii\akismet\{Author, Client, Comment};

$client = new Client([
  'apiKey' => '123YourAPIKey',
  'blog' => 'http://www.yourblog.com'
]);

$author = new Author('127.0.0.1', 'Mozilla/5.0', ['role' => 'administrator']);
$comment = new Comment($author, 'A user comment');

$isSpam = $client->checkComment($comment);
print("It should be 'false': $isSpam");
```

## Automated testing
Enable the `Client->isTest` option in your tests.

That will tell Akismet not to change its behaviour based on those API calls – they will have no training effect. That means your tests will be somewhat repeatable, in the sense that one test won't influence subsequent calls.

```php
<?php
use yii\akismet\{Author, Client, Comment};

$client = new Client([
  'apiKey' => '123YourAPIKey',
  'blog' => 'http://www.yourblog.com',
  'isTest' => true
]);

$author = new Author('127.0.0.1', 'Mozilla/5.0');
$comment = new Comment($author, 'A user comment');

echo 'It should not influence subsequent calls';
$client->checkComment($comment);
```
