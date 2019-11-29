<?php

declare(strict_types=1);

namespace BeatportOauth;

use GuzzleHttp\HandlerStack;
use Psr\SimpleCache\CacheInterface;

class OauthMiddlewareFactory
{
    public const MIDDLEWARE_NAME = OauthMiddleware::NAME;

    public static function create(array $oauthParams, ?HandlerStack $stack = null): OauthMiddleware
    {
        $accessTokenProvider = new AccessTokenProvider($oauthParams, $stack);

        return static::createFromAccessTokenProvider($accessTokenProvider);
    }

    public static function createWithCachedToken(
        array $oauthParams,
        CacheInterface $cache,
        array $cacheConfig = [],
        ?HandlerStack $stack = null
    ): OauthMiddleware {
        $accessTokenProvider = new AccessTokenProvider($oauthParams, $stack);
        $cachedAccessTokenProvider = new CachedAccessTokenProvider($accessTokenProvider, $cache, $cacheConfig);

        return static::createFromAccessTokenProvider($cachedAccessTokenProvider);
    }

    public static function createFromAccessTokenProvider(
        AccessTokenProviderInterface $accessTokenProvider
    ): OauthMiddleware {
        return new OauthMiddleware($accessTokenProvider);
    }
}
