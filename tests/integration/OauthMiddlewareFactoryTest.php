<?php

declare(strict_types=1);

namespace BeatportOauth\Tests\Integration;

use BeatportOauth\AccessTokenProvider;
use BeatportOauth\CachedAccessTokenProvider;
use BeatportOauth\OauthMiddleware;
use BeatportOauth\OauthMiddlewareFactory;
use Cache\Adapter\PHPArray\ArrayCachePool;
use Closure;
use GuzzleHttp\HandlerStack;

class OauthMiddlewareFactoryTest extends AbstractIntegrationTestCase
{
    public function testCreate(): void
    {
        $oauthParams = static::getOauthParams();
        $stack = HandlerStack::create();

        $middleware = OauthMiddlewareFactory::create($oauthParams, $stack);

        static::assertInstanceOf(OauthMiddleware::class, $middleware);
    }

    public function testCreateWithCachedToken(): void
    {
        $oauthParams = static::getOauthParams();
        $cache = new ArrayCachePool();
        $cacheConfig = [ 'key' => 'my-access-token-info' ];
        $stack = HandlerStack::create();

        $middleware = OauthMiddlewareFactory::createWithCachedToken(
            $oauthParams,
            $cache,
            $cacheConfig,
            $stack
        );

        static::assertInstanceOf(OauthMiddleware::class, $middleware);
    }

    /**
     * @param Closure $getAccessTokenProvider
     *
     * @dataProvider dataCreateFromAccessTokenProvider
     */
    public function testCreateFromAccessTokenProvider($getAccessTokenProvider): void
    {
        $accessTokenProvider = $getAccessTokenProvider();

        $middleware = OauthMiddlewareFactory::createFromAccessTokenProvider($accessTokenProvider);

        static::assertInstanceOf(OauthMiddleware::class, $middleware);
    }

    public function dataCreateFromAccessTokenProvider(): array
    {
        return [
            'create with access token' => [function () {
                $oauthParams = static::getOauthParams();

                return new AccessTokenProvider($oauthParams);
            }],

            'create with cached access token' => [function () {
                $oauthParams = static::getOauthParams();
                $cache = new ArrayCachePool();

                return CachedAccessTokenProvider::factory($oauthParams, $cache);
            }],
        ];
    }
}
