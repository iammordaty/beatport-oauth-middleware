<?php

declare(strict_types=1);

namespace BeatportOauth\Tests\Integration;

use PHPUnit\Framework\TestCase;

class AbstractIntegrationTestCase extends TestCase
{
    private static $oauthParams = [];

    public static function setUpBeforeClass(): void
    {
        self::$oauthParams = [
            'consumer_key' => $_ENV['consumer_key'],
            'consumer_secret' => $_ENV['consumer_secret'],
            'username' => $_ENV['username'],
            'password' => $_ENV['password'],
        ];
    }

    public function setUp(): void
    {
        if (!self::hasValidSetUp()) {
            static::markTestSkipped('Oauth params not set, skipping.');
        }
    }

    protected static function getOauthParams(): array
    {
        return self::$oauthParams;
    }

    private static function hasValidSetUp(): bool
    {
        // TODO: czy wszystko uzupełnione?

        return true;
    }
}
