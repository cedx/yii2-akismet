# Events
The `yii\akismet\Client` class, used to query the Akismet service, triggers some events during its life cycle.

## The "request" event
Emitted every time a request is made to the remote service:

```php
use yii\akismet\Client;
use yii\httpclient\RequestEvent;

$client->on(Client::eventRequest, fn(RequestEvent $event) =>
	print "Client request: {$event->request->url}"
);
```

## The "response" event
Emitted every time a response is received from the remote service:

```php
use yii\akismet\Client;
use yii\httpclient\RequestEvent;

$client->on(Client::eventResponse, fn(RequestEvent $event) =>
	print "Server response: {$event->response->statusCode}"
);
```
