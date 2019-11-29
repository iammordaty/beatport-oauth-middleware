<?php

declare(strict_types=1);

namespace BeatportOauth;

use Closure;
use GuzzleHttp\Subscriber\Oauth\Oauth1;

class OauthMiddleware
{
    public const NAME = 'beatport-oauth-middleware';

    /**
     * @var AccessTokenProviderInterface
     */
    private $accessTokenProvider;

    /**
     * @param AccessTokenProviderInterface $oauthSubscriber
     */
    public function __construct(AccessTokenProviderInterface $oauthSubscriber)
    {
        $this->accessTokenProvider = $oauthSubscriber;
    }

    public function __invoke(callable $handler): Closure
    {
        $accessTokenInfo = $this->accessTokenProvider->getAccessTokenInfo();
        $subscriber = new Oauth1($accessTokenInfo);

        return $subscriber($handler);
    }
}
