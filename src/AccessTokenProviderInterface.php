<?php

declare(strict_types=1);

namespace BeatportOauth;

interface AccessTokenProviderInterface
{
    public function getAccessTokenInfo(): array;
}
