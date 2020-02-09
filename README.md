# Akismet for Yii
![PHP](https://img.shields.io/packagist/php-v/cedx/yii2-akismet.svg) ![Yii Framework](https://img.shields.io/badge/yii-%3E%3D2.0-brightgreen.svg) ![Release](https://img.shields.io/packagist/v/cedx/yii2-akismet.svg) ![License](https://img.shields.io/packagist/l/cedx/yii2-akismet.svg) ![Downloads](https://img.shields.io/packagist/dt/cedx/yii2-akismet.svg) ![Coverage](https://coveralls.io/repos/github/cedx/yii2-akismet/badge.svg) ![Build](https://github.com/cedx/yii2-akismet/workflows/build/badge.svg)

Prevent comment spam using the [Akismet](https://akismet.com) connector for [Yii](https://www.yiiframework.com), high-performance [PHP](https://www.php.net) framework.

## Documentation
- [User guide](https://dev.belin.io/yii2-akismet)
- [API reference](https://dev.belin.io/yii2-akismet/api)

## Development
- [Git repository](https://github.com/cedx/yii2-akismet)
- [Packagist package](https://packagist.org/packages/cedx/yii2-akismet)
- [Submit an issue](https://github.com/cedx/yii2-akismet/issues)

## Features
- [Key verification](https://akismet.com/development/api/#verify-key): checks an Akismet API key and gets a value indicating whether it is valid.
- [Comment check](https://akismet.com/development/api/#comment-check): checks a comment and gets a value indicating whether it is spam.
- [Submit spam](https://akismet.com/development/api/#submit-spam): submits a comment that was not marked as spam but should have been.
- [Submit ham](https://akismet.com/development/api/#submit-ham): submits a comment that was incorrectly marked as spam but should not have been.

## Requirements
The latest [PHP](https://www.php.net) and [Composer](https://getcomposer.org) versions to use the Akismet library.

If you plan to play with the sources, you will also need the latest [Robo](https://robo.li) and [Material for MkDocs](https://squidfunk.github.io/mkdocs-material) versions.

## Installing via [Composer](https://getcomposer.org)
From a command prompt, run:

```shell
composer require cedx/yii2-akismet
```

## Usage
In your application configuration file, you can use the following component:

```php
<?php return [
  'components' => [
    'akismet' => [
      'class' => 'yii\akismet\http\Client',
      'apiKey' => '123YourAPIKey',
      'blog' => [
        'class' => 'yii\akismet\Blog',
        'url' => 'http://www.yourblog.com'
      ]
    ]
  ]
];
```

Once the `yii\akismet\http\Client` component initialized with your credentials, you can use its methods.

### Key verification

```php
<?php
use yii\akismet\http\{ClientException};

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
    ['content' => 'A user comment', 'date' => new \DateTimeImmutable]
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

## License
[Akismet for Yii](https://dev.belin.io/yii2-akismet) is distributed under the MIT License.
