path: blob/master/lib
source: Client.php

# Submit ham
This call is intended for the submission of false positives - items that were incorrectly classified as spam by Akismet. It takes identical arguments as [comment check](comment_check.md) and [submit spam](submit_spam.md).

Remember that, as explained in the [submit spam](submit_spam.md) documentation, you should ensure that any values you're passing here match up with the original and corresponding [comment check](comment_check.md) call.

```
Client->submitHam(Comment $comment): void
```

## Example

```php
<?php
use yii\akismet\{Author, Client, ClientException, Comment, CommentType};

try {
  $client = new Client([
    'apiKey' => '123YourAPIKey',
    'blog' => 'http://www.yourblog.com'
  ]);

  $comment = new Comment(
    new Author('127.0.0.1', 'Mozilla/5.0'),
    'A user comment',
    CommentType::CONTACT_FORM
  );

  $client->submitHam($comment);
  echo 'Ham submitted';
}

catch (ClientException $e) {
  echo 'An error occurred: ', $e->getMessage();
}
```
