# beatport-oauth-middleware

Guzzle 6.x OAuth middleware for [Beatport](http://beatport.com) API. Allows server-side querying and access token caching.

## Installation

The easiest way to install this middleware is via [composer](https://getcomposer.org):

```bash
$ composer require iammordaty/beatport-oauth-middleware
```

### Requirements

* PHP 7.1+
* Beatport's Consumer Key, Consumer Secret and account credentials

For more information about Consumer Key, Consumer Secret see [Beatport announcement](https://groups.google.com/forum/#!topic/beatport-api/sU8TCHEOpuY) 
and visit [Beatport API documentation](https://oauth-api.beatport.com/).

### Usage

The following example demonstrates how to initialize a Guzzle client with middleware,
and then retrieve information from Beatport API about the track based on its ID.

```php
use BeatportOauth\AccessTokenProvider;
use BeatportOauth\OauthMiddlewareFactory;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

$oauthParams = [
   'consumer_key' => $_ENV['consumer_key'],
   'consumer_secret' => $_ENV['consumer_secret'],
   'username' => $_ENV['username'],
   'password' => $_ENV['password'],
];

$middleware = OauthMiddlewareFactory::create($oauthParams); 

$stack = HandlerStack::create();
$stack->push($middleware);

$client = new Client([
    'auth' => 'oauth',
    'base_uri' => AccessTokenProvider::BASE_URI,
    'handler' => $stack,
]);

$response = $client->get('catalog/3/tracks', [
    'query' => [ 'id' => 12387959 ],
]);

$contents = json_decode($response->getBody()->getContents(), true);
```

In addition, it is also possible to cache access token for later use.
// or, with a token that will be stored in :
```php
use BeatportOauth\OauthMiddlewareFactory;
use Cache\Adapter\PHPArray\ArrayCachePool;
use GuzzleHttp\HandlerStack;

$oauthParams = [/* */];
$cache = new ArrayCachePool(); // or any other PSR-16 compatible cache pool
$cacheConfig = [ 'key' => 'my-access-token-info' ]; // optional

$middleware = OauthMiddlewareFactory::createWithCachedToken(
    $oauthParams,
    $cache,
    $cacheConfig
);

$stack = HandlerStack::create();
$stack->push($middleware);
```

## Tests

Copy `phpunit.xml.dist` file to `phpunit.xml` and fill in the missing parameters.
Now you can test middleware by running the following command:

```bash
$ ./vendor/bin/phpunit
```

## Further information

- [Beatport API documentation](https://oauth-api.beatport.com)
- [Beatport announcement](https://groups.google.com/forum/#!topic/beatport-api/sU8TCHEOpuY) of API key acquisition

## License

iammordaty/beatport-oauth-middleware is licensed under the MIT License.
