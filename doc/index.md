# Akismet <small>for Yii</small>
![PHP](https://img.shields.io/packagist/php-v/cedx/yii2-akismet.svg) ![Yii Framework](https://img.shields.io/badge/yii-%3E%3D2.0-brightgreen.svg) ![Release](https://img.shields.io/packagist/v/cedx/yii2-akismet.svg) ![License](https://img.shields.io/packagist/l/cedx/yii2-akismet.svg) ![Downloads](https://img.shields.io/packagist/dt/cedx/yii2-akismet.svg) ![Coverage](https://coveralls.io/repos/github/cedx/yii2-akismet/badge.svg) ![Build](https://github.com/cedx/yii2-akismet/workflows/build/badge.svg)

![Akismet](img/akismet.png)

## Stop spam
Used by millions of websites, [Akismet](https://akismet.com) filters out hundreds of millions of spam comments from the Web every day.
Add Akismet to your [Yii Framework](https://www.yiiframework.com) applications so you don't have to worry about spam again.

## Quick start

### Get a developer key
You first need to [sign up for a developer key](https://akismet.com/signup/?plan=developer).
This will give you access to the API and will allow Akismet to monitor its results to make sure things are running as smoothly as possible.

!!! warning
    All Akismet endpoints require an API key. If you are not already registered,
    [join the developer program](https://akismet.com/signup/?plan=developer).

### Get the library
Install the latest version of **Akismet for Yii** with [Composer](https://getcomposer.org):

```shell
composer require cedx/yii2-akismet
```

For detailed instructions, see the [installation guide](installation.md).
