<?php

declare(strict_types=1);

namespace BeatportOauth\Tests\Integration;

use BeatportOauth\AccessTokenProvider;

class AccessTokenProviderTest extends AbstractIntegrationTestCase
{
    public function testCanObtainAccessTokenInfo(): void
    {
        $oauthParams = static::getOauthParams();

        $accessTokenProvider = new AccessTokenProvider($oauthParams);
        $accessTokenInfo = $accessTokenProvider->getAccessTokenInfo();

        $required = [ 'consumer_key', 'consumer_secret',  'token', 'token_secret' ];

        foreach ($required as $field) {
            static::assertArrayHasKey($field, $accessTokenInfo);
            static::assertNotEmpty($accessTokenInfo[$field]);
        }
    }
}
