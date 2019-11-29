<?php

declare(strict_types=1);

namespace BeatportOauth\Tests\Integration;

use BeatportOauth\AccessTokenProvider;
use BeatportOauth\OauthMiddlewareFactory;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Teapot\StatusCode;

class OauthMiddlewareTest extends AbstractIntegrationTestCase
{
    public function testInvoke(): void
    {
        $oauthParams = self::getOauthParams();
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

        static::assertSame(StatusCode::OK, $response->getStatusCode());
        static::assertSame('application/json', $response->getHeaderLine('Content-Type'));

        $contents = json_decode($response->getBody()->getContents(), true);

        static::assertArrayHasKey('metadata', $contents);
        static::assertArrayHasKey('results', $contents);

        static::assertNotEmpty($contents['results'][0]);

        static::assertSame($contents['results'][0]['id'], 12387959);
    }
}
