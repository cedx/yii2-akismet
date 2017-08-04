# Changelog
This file contains highlights of what changes on each version of the [Akismet for Yii](https://github.com/cedx/yii2-akismet) library.

## Version 5.0.0
- Breaking change: properties representing URLs as strings now use instances of the [`Psr\Http\Message\UriInterface`](http://www.php-fig.org/psr/psr-7/#35-psrhttpmessageuriinterface) interface.
- Breaking change: the `Blog::$languages` property is now an instance of `ArrayObject` class.
- Added new unit tests.
- The `$date` and `$postModified` properties of the `Comment` class now accept strings and timestamps.

## Version 4.2.0
- Added a `fromJson()` static method to the data classes.
- Added new unit tests.
- Enabled the strict typing.
- Replaced [phpDocumentor](https://www.phpdoc.org) documentation generator by [ApiGen](https://github.com/ApiGen/ApiGen).
- Updated the package dependencies.

## Version 4.1.0
- Removed the dependency on the `cedx/yii2-core` module.

## Version 4.0.0
- Breaking change: removed the `RequestEvent` and `RequestResponse` classes.
- Breaking change: renamed the `Client::EVENT_REQUEST` to `EVENT_BEFORE_SEND`.
- Breaking change: renamed the `Client::EVENT_RESPONSE` to `EVENT_AFTER_SEND`.
- Breaking change: replaced most of getters and setters by properties.
- Added the `DEBUG_HEADER`, `DEFAULT_ENDPOINT` and `VERSION` constants to the `Client` class.
- Added the `CommentType` enumeration.
- Added validation rules to the data classes.
- Dropped the dependency on the `cedx/akismet` module.
- The data classes now extend from `yii\base\Model`.

## Version 3.1.0
- Ported the unit test assertions from [TDD](https://en.wikipedia.org/wiki/Test-driven_development) to [BDD](https://en.wikipedia.org/wiki/Behavior-driven_development).
- Updated the package dependencies.

## Version 3.0.0
- Breaking change: changed the type of the `Blog::languages` property to `ArrayObject`.
- Added the `Client::endPoint` property.
- Updated the package dependencies.

## Version 2.0.0
- Breaking change: changed the `Blog::language` string property for the `languages` array property.
- Removed the `dist` build task.
- Replaced the [Codacy](https://www.codacy.com) code coverage service by the [Coveralls](https://coveralls.io) one.
- Updated the package dependencies.

## Version 1.1.0
- Added the `RequestEvent` and `ResponseEvent` events.

## Version 1.0.0
- Initial release.
