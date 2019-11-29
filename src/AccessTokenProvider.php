<?php

declare(strict_types=1);

namespace BeatportOauth;

use BeatportOauth\Exception\TokenMismatchException;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use GuzzleUrlEncodedResponseMiddleware\UrlEncodedResponseMiddleware;
use Iammordaty\GuzzleHttp\ClientFactory;
use Iammordaty\GuzzleHttp\HandlerStackFactory;

final class AccessTokenProvider implements AccessTokenProviderInterface
{
    public const BASE_URI = 'https://oauth-api.beatport.com';

    private const OUT_OF_BAND_CALLBACK = 'oob';

    /**
     * @var array
     */
    private $oauthParams;

    /**
     * @var HandlerStack
     */
    private $stack;

    public function __construct(array $oauthParams, ?HandlerStack $stack = null)
    {
        $this->oauthParams = $oauthParams;
        $this->stack = $stack ?: HandlerStackFactory::create();
    }

    public function getAccessTokenInfo(): array
    {
        // First leg

        $oauth = new Oauth1([
            'consumer_key' => $this->oauthParams['consumer_key'],
            'consumer_secret' => $this->oauthParams['consumer_secret'],
            'token_secret' => '',
        ]);

        $client = $this->getClient($oauth);

        $requestTokenInfo = static::post($client, 'identity/1/oauth/request-token', [
            'oauth_callback' => self::OUT_OF_BAND_CALLBACK,
        ]);

        // Second leg

        $authorizeTokenInfo = self::post($client, 'identity/1/oauth/authorize-submit', [
            'oauth_token' => $requestTokenInfo['oauth_token'],
            'username' => $this->oauthParams['username'],
            'password' => $this->oauthParams['password'],
            'submit' => 'Login',
        ]);

        if ($requestTokenInfo['oauth_token'] !== $authorizeTokenInfo['oauth_token']) {
            throw new TokenMismatchException('Request token and authorization token do not match.');
        }

        // Third leg

        $oauth = new Oauth1([
            'consumer_key' => $this->oauthParams['consumer_key'],
            'consumer_secret' => $this->oauthParams['consumer_secret'],
            'token' => $requestTokenInfo['oauth_token'],
            'token_secret' => $requestTokenInfo['oauth_token_secret'],
        ]);

        $client = $this->getClient($oauth);

        $accessTokenInfo = self::post($client, 'identity/1/oauth/access-token', [
            'oauth_verifier' => $authorizeTokenInfo['oauth_verifier'],
        ]);

        return [
            'consumer_key' => $this->oauthParams['consumer_key'],
            'consumer_secret' => $this->oauthParams['consumer_secret'],
            'token' => $accessTokenInfo['oauth_token'],
            'token_secret' => $accessTokenInfo['oauth_token_secret'],
            'session_id' => $accessTokenInfo['session_id'],
        ];
    }

    private function getClient(Oauth1 $oauth): Client
    {
        $stack = clone $this->stack;
        $stack->push($oauth, 'oauth');
        $stack->push(new UrlEncodedResponseMiddleware(), UrlEncodedResponseMiddleware::NAME);

        $client = ClientFactory::create([
            'auth' => 'oauth',
            'base_uri' => self::BASE_URI,
            'stack' => $stack,
        ]);

        return $client;
    }

    private static function post(Client $client, string $uri, array $params): array
    {
        $response = $client->post($uri, [ 'form_params' => $params ]);
        $contents = $response->getBody()->getUrlDecodedParsedContents();

        return $contents;
    }
}
