# Akismet for Yii
![Runtime](https://img.shields.io/badge/php-%3E%3D7.0-brightgreen.svg) ![Release](https://img.shields.io/packagist/v/cedx/yii2-akismet.svg) ![License](https://img.shields.io/packagist/l/cedx/yii2-akismet.svg) ![Downloads](https://img.shields.io/packagist/dt/cedx/yii2-akismet.svg) ![Coverage](https://coveralls.io/repos/github/cedx/yii2-akismet/badge.svg) ![Build](https://travis-ci.org/cedx/yii2-akismet.svg)

Prevent comment spam using the [Akismet](https://akismet.com) connector for [Yii](http://www.yiiframework.com), high-performance [PHP](https://secure.php.net) framework.

## Features
- [Key verification](https://akismet.com/development/api/#verify-key): checks an Akismet API key and gets a value indicating whether it is valid.
- [Comment check](https://akismet.com/development/api/#comment-check): checks a comment and gets a value indicating whether it is spam.
- [Submit spam](https://akismet.com/development/api/#submit-spam): submits a comment that was not marked as spam but should have been.
- [Submit ham](https://akismet.com/development/api/#submit-ham): submits a comment that was incorrectly marked as spam but should not have been.

## Requirements
The latest [PHP](https://secure.php.net) and [Composer](https://getcomposer.org) versions.
If you plan to play with the sources, you will also need the latest [Phing](https://www.phing.info) version.

## Installing via [Composer](https://getcomposer.org)
From a command prompt, run:

```shell
$ composer global require fxp/composer-asset-plugin
$ composer require cedx/yii2-akismet
```

## Usage
In your application configuration file, you can use the following component:

```php
use yii\akismet\{Client};

return [
  'components' => [
    'akismet' => [
      'class' => Client::class,
      'apiKey' => 'YourAPIKey',
      'blog' => 'http://your.blog.url'
    ]
  ]
];
```

Once the `yii\akismet\Client` component initialized with your credentials, you can use its methods.


### Key verification

```php
try {
  $client = \Yii::$app->akismet;
  echo $client->verifyKey() ? 'Your API key is valid.' : 'Your API key is invalid.';
}

catch (\Throwable $e) {
  echo 'An error occurred: ', $e->getMessage();
}
```

### Comment check

```php
use yii\akismet\{Author, Comment};

try {
  $comment = new Comment(
    new Author('127.0.0.1', 'Mozilla/5.0'),
    ['content' => 'A comment.', 'date' => time()]
  );
    
  $isSpam = $client->checkComment($comment);
  echo $isSpam ? 'The comment is marked as spam.' : 'The comment is marked as ham.';
}

catch (\Throwable $e) {
  echo 'An error occurred: ', $e->getMessage();
}
```

### Submit spam/ham

```php
try {
  $client->submitSpam($comment);
  echo 'Spam submitted.';
    
  $client->submitHam($comment);
  echo 'Ham submitted.';
}

catch (\Throwable $e) {
  echo 'An error occurred: ', $e->getMessage();
}
```

## Events
The `yii\akismet\Client` class triggers some events during its life cycle.

### The `beforeSend` event
Emitted every time a request is made to the remote service:

```php
use yii\akismet\{Client};
use yii\httpclient\{RequestEvent};

$client->on(Client::EVENT_BEFORE_SEND, function(RequestEvent $event) {
  echo 'Client request: ', $event->request->url;
});
```

### The `afterSend` event
Emitted every time a response is received from the remote service:

```php
use yii\akismet\{Client};
use yii\httpclient\{RequestEvent};

$client->on(Client::EVENT_AFTER_SEND, function(RequestEvent $event) {
  echo 'Server response: ', $event->response->statusCode;
});
```

## Unit tests
In order to run the tests, you must set the `AKISMET_API_KEY` environment variable to the value of your Akismet API key:

```shell
$ export AKISMET_API_KEY="<YourAPIKey>"
```

Then, you can run the `test` script from the command prompt:

```shell
$ composer test
```

## See also
- [API reference](https://cedx.github.io/yii2-akismet)
- [Code coverage](https://coveralls.io/github/cedx/yii2-akismet)
- [Continuous integration](https://travis-ci.org/cedx/yii2-akismet)

## License
[Akismet for Yii](https://github.com/cedx/yii2-akismet) is distributed under the MIT License.
