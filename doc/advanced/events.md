path: blob/master
source: lib/http/Client.php

# Events
The `yii\akismet\http\Client`, used to query the Akismet service, class triggers some events during its life cycle.

### The `Client::eventRequest` event
Emitted every time a request is made to the remote service:

```php
<?php
use yii\akismet\http\{Client};
use yii\httpclient\{RequestEvent};

$client->on(Client::eventRequest, fn(RequestEvent $event) =>
  echo 'Client request: ', $event->request->url
);
```

### The `Client::eventResponse` event
Emitted every time a response is received from the remote service:

```php
<?php
use yii\akismet\http\{Client};
use yii\httpclient\{RequestEvent};

$client->on(Client::eventResponse, fn(RequestEvent $event) =>
  echo 'Server response: ', $event->response->statusCode
);
```
