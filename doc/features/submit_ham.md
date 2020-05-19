# Submit ham
This call is intended for the submission of false positives - items that were incorrectly classified as spam by Akismet.
It takes identical arguments as [comment check](comment_check.md) and [submit spam](submit_spam.md).

```
Client->submitHam(Comment $comment): void
```

Remember that, as explained in the [submit spam](submit_spam.md) documentation, you should ensure
that any values you're passing here match up with the original and corresponding [comment check](comment_check.md) call.

See the [Akismet API documentation](https://akismet.com/development/api/#submit-ham) for more information.

## Parameters

### Comment **$comment**
The user `Comment` to be submitted, incorrectly classified as spam.

!!! tip
	Ideally, it should be the same object as the one passed to the original [comment check](comment_check.md) API call.

## Return value
None.

The method throws a `ClientException` when an error occurs.
The exception `getMessage()` usually includes some debug information, provided by the `X-akismet-debug-help` HTTP header, about what exactly was invalid about the call.

## Example

``` php
<?php
use yii\akismet\{Author, Client, ClientException, Comment};

try {
	$client = new Client([
		"apiKey" => "123YourAPIKey",
		"blog" => "http://www.yourblog.com"
	]);

	$comment = new Comment(
		new Author("127.0.0.1", "Mozilla/5.0"),
		["content" => "A valid user comment (ham)"]
	);

	$result = $client->checkComment($comment); // `true`, but `false` expected.
		// Got `CheckResult::isSpam`, but `CheckResult::isHam` expected.

	echo "The comment was incorrectly classified as spam.";
	$client->submitHam($comment);
}

catch (ClientException $e) {
	echo "An error occurred: ", $e->getMessage();
}
```
