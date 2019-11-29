<?php

declare(strict_types=1);

namespace BeatportOauth;

use GuzzleHttp\HandlerStack;
use Psr\SimpleCache\CacheInterface;

final class CachedAccessTokenProvider implements AccessTokenProviderInterface
{
    private const DEFAULT_CACHE_CONFIG = [
        'key' => 'beatport-access-token-info',
        'ttl' => null,
    ];

    /**
     * @var AccessTokenProviderInterface
     */
    private $accessTokenProvider;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var array
     */
    private $cacheConfig;

    /**
     * @param AccessTokenProviderInterface $accessTokenProvider
     * @param CacheInterface $cache
     * @param array $cacheConfig
     */
    public function __construct(
        AccessTokenProviderInterface $accessTokenProvider,
        CacheInterface $cache,
        array $cacheConfig = []
    ) {
        $this->accessTokenProvider = $accessTokenProvider;
        $this->cache = $cache;
        $this->cacheConfig = array_merge(self::DEFAULT_CACHE_CONFIG, $cacheConfig);
    }

    public static function factory(
        array $oauthParams,
        CacheInterface $cache,
        array $cacheConfig = [],
        ?HandlerStack $stack = null
    ) {
        $accessTokenProvider = new AccessTokenProvider($oauthParams, $stack);

        return new self($accessTokenProvider, $cache, $cacheConfig);
    }

    public function getAccessTokenInfo(): array
    {
        [ 'key' => $cacheKey, 'ttl' => $cacheTtl ] = $this->cacheConfig;

        $accessTokenInfo = $this->cache->get($cacheKey);

        if (!$accessTokenInfo) {
            $accessTokenInfo = $this->accessTokenProvider->getAccessTokenInfo();

            $this->cache->set($cacheKey, $accessTokenInfo, $cacheTtl);
        }

        return $accessTokenInfo;
    }
}
