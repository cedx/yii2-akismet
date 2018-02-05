# Akismet for Yii
![Runtime](https://img.shields.io/badge/php-%3E%3D7.1-brightgreen.svg) ![Release](https://img.shields.io/packagist/v/cedx/yii2-akismet.svg) ![License](https://img.shields.io/packagist/l/cedx/yii2-akismet.svg) ![Downloads](https://img.shields.io/packagist/dt/cedx/yii2-akismet.svg) ![Coverage](https://coveralls.io/repos/github/cedx/yii2-akismet/badge.svg) ![Build](https://travis-ci.org/cedx/yii2-akismet.svg)

Prevent comment spam using the [Akismet](https://akismet.com) connector for [Yii](http://www.yiiframework.com), high-performance [PHP](https://secure.php.net) framework.

> For detailed instructions, see the [user guide](https://cedx.github.io/yii2-akismet).

## Features
- [Key verification](https://akismet.com/development/api/#verify-key): checks an Akismet API key and gets a value indicating whether it is valid.
- [Comment check](https://akismet.com/development/api/#comment-check): checks a comment and gets a value indicating whether it is spam.
- [Submit spam](https://akismet.com/development/api/#submit-spam): submits a comment that was not marked as spam but should have been.
- [Submit ham](https://akismet.com/development/api/#submit-ham): submits a comment that was incorrectly marked as spam but should not have been.

## Requirements
The latest [PHP](https://secure.php.net) and [Composer](https://getcomposer.org) versions to use the Akismet library.

If you plan to play with the sources, you will also need the latest [Phing](https://www.phing.info) and [Material for MkDocs](https://squidfunk.github.io/mkdocs-material) versions.

## Installing via [Composer](https://getcomposer.org)
From a command prompt, run:

```shell
composer global require fxp/composer-asset-plugin
composer require cedx/yii2-akismet
```

## Usage
In your application configuration file, you can use the following component:

```php
<?php
use yii\akismet\{Client};

return [
  'components' => [
    'akismet' => [
      'class' => Client::class,
      'apiKey' => '123YourAPIKey',
      'blog' => 'http://www.yourblog.com'
    ]
  ]
];
```

Once the `yii\akismet\Client` component initialized with your credentials, you can use its methods.

### Key verification

```php
<?php
use yii\akismet\{ClientException};

try {
  $client = \Yii::$app->akismet;
  $isValid = $client->verifyKey();
  echo $isValid ? 'The API key is valid' : 'The API key is invalid';
}

catch (ClientException $e) {
  echo 'An error occurred: ', $e->getMessage();
}
```

### Comment check

```php
<?php
use yii\akismet\{Author, Comment};

try {
  $comment = new Comment(
    new Author('127.0.0.1', 'Mozilla/5.0'),
    ['content' => 'A user comment', 'date' => time()]
  );
      
  $isSpam = $client->checkComment($comment);
  echo $isSpam ? 'The comment is spam' : 'The comment is ham';
}

catch (ClientException $e) {
  echo 'An error occurred: ', $e->getMessage();
}
```

### Submit spam / ham

```php
<?php
try {
  $client->submitSpam($comment);
  echo 'Spam submitted';
    
  $client->submitHam($comment);
  echo 'Ham submitted';
}

catch (ClientException $e) {
  echo 'An error occurred: ', $e->getMessage();
}
```

## Events
The `yii\akismet\Client` class triggers some events during its life cycle.

### The `request` event
Emitted every time a request is made to the remote service:

```php
<?php
use yii\akismet\{Client};
use yii\httpclient\{RequestEvent};

$client->on(Client::EVENT_REQUEST, function(RequestEvent $event) {
  echo 'Client request: ', $event->request->url;
});
```

### The `response` event
Emitted every time a response is received from the remote service:

```php
<?php
use yii\akismet\{Client};
use yii\httpclient\{RequestEvent};

$client->on(Client::EVENT_RESPONSE, function(RequestEvent $event) {
  echo 'Server response: ', $event->response->statusCode;
});
```

## Unit tests
In order to run the tests, you must set the `AKISMET_API_KEY` environment variable to the value of your Akismet API key:

```shell
export AKISMET_API_KEY="<123YourAPIKey>"
```

Then, you can run the `test` script from the command prompt:

```shell
composer test
```

## See also
- [API reference](https://cedx.github.io/yii2-akismet/api)
- [Packagist package](https://packagist.org/packages/cedx/yii2-akismet)
- [Continuous integration](https://travis-ci.org/cedx/yii2-akismet)
- [Code coverage](https://coveralls.io/github/cedx/yii2-akismet)

### Other implementations
- Dart: [Akismet for Dart](https://cedx.github.io/akismet.dart)
- Node.js: [Akismet for JS](https://cedx.github.io/akismet.js)
- PHP: [Akismet for PHP](https://cedx.github.io/akismet.php)

## License
[Akismet for Yii](https://cedx.github.io/yii2-akismet) is distributed under the MIT License.
