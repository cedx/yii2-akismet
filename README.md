# Akismet for Yii
![Release](https://img.shields.io/packagist/v/cedx/yii2-akismet.svg) ![License](https://img.shields.io/packagist/l/cedx/yii2-akismet.svg) ![Downloads](https://img.shields.io/packagist/dt/cedx/yii2-akismet.svg) ![Code quality](https://img.shields.io/codacy/grade/a0b840d5ed7944849947331e5ae18157.svg) ![Build](https://img.shields.io/travis/cedx/yii2-akismet.svg)

Prevent comment spam using the [Akismet](https://akismet.com) connector for [Yii](http://www.yiiframework.com), high-performance [PHP](https://secure.php.net) framework.

## Features
- [Key Verification](https://akismet.com/development/api/#verify-key): checks an Akismet API key and gets a value indicating whether it is valid.
- [Comment Check](https://akismet.com/development/api/#comment-check): checks a comment and gets a value indicating whether it is spam.
- [Submit Spam](https://akismet.com/development/api/#submit-spam): submits a comment that was not marked as spam but should have been.
- [Submit Ham](https://akismet.com/development/api/#submit-ham): submits a comment that was incorrectly marked as spam but should not have been.

## Requirements
The latest [PHP](https://secure.php.net) and [Composer](https://getcomposer.org) versions.
If you plan to play with the sources, you will also need the [Phing](https://www.phing.info) latest version.

## Installing via [Composer](https://getcomposer.org)
From a command prompt, run:

```shell
$ composer require cedx/yii2-akismet
```

## Usage
In your application configuration file, you can use the following component:

```php
return [
  'components' => [
    'akismet' => [
      'class' => 'yii\akismet\Client',
      'apiKey' => 'YourAPIKey',
      'blog' => 'http://your.blog.url'
    ]
  ]
];
```

Once the `yii\akismet\Client` component initialized with your credentials, you can use its methods.


### Key Verification

```php
$client = \Yii::$app->get('akismet');

$isValid = $client->verifyKey();
echo $isValid ? 'Your API key is valid.' : 'Your API key is invalid.';
```

### Comment Check

```php
use yii\akismet\{Author, Comment};

$comment = new Comment([
  'author' => new Author(['ipAddress' => '127.0.0.1', 'userAgent' => 'Mozilla/5.0']),
  'content' => 'A comment.'
]);

$isSpam = $client->checkComment($comment);
echo $isSpam ? 'The comment is marked as spam.' : 'The comment is marked as ham.';
```

### Submit Spam/Ham

```php
$client->submitSpam($comment);
echo 'Spam submitted.';

$client->submitHam($comment);
echo 'Ham submitted.';
```

## Events
The `yii\akismet\Client` class triggers some events during its life cycle.

### The `request` event
Emitted every time a request is made to the remote service:

```php
use yii\akismet\{Client, RequestEvent};

$client->on(Client::EVENT_REQUEST, function(RequestEvent $event) {
  echo 'Client request: ', $event->getRequest()->getUri();
});
```

### The `response` event
Emitted every time a response is received from the remote service:

```php
use yii\akismet\{Client, ResponseEvent};

$client->on(Client::EVENT_RESPONSE, function(ResponseEvent $event) {
  echo 'Server response: ', $event->getResponse()->getStatusCode();
});
```

## Unit Tests
In order to run the tests, you must set the `AKISMET_API_KEY` environment variable to the value of your Akismet API key:

```shell
$ export AKISMET_API_KEY="<YourAPIKey>"
```

Then, you can run the `test` script from the command prompt:

```shell
$ phing test
```

## See Also
- [Code Quality](https://www.codacy.com/app/cedx/yii2-akismet)
- [Continuous Integration](https://travis-ci.org/cedx/yii2-akismet)

## License
[Akismet for Yii](https://github.com/cedx/yii2-akismet) is distributed under the Apache License, version 2.0.
