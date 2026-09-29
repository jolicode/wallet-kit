<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Api\Auth;

use Jolicode\WalletKit\Api\Credentials\GoogleCredentials;
use Jolicode\WalletKit\Exception\Api\AuthenticationException;
use Jolicode\WalletKit\Exception\Api\HttpRequestException;
use Jolicode\WalletKit\Exception\Api\MissingExtensionException;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class GoogleOAuth2Authenticator
{
    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';
    private const SCOPE = 'https://www.googleapis.com/auth/wallet_object.issuer';

    private ?CachedToken $cachedToken = null;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly GoogleCredentials $credentials,
    ) {
        if (!\extension_loaded('openssl')) {
            throw new MissingExtensionException('The "openssl" PHP extension is required for Google OAuth2 authentication.');
        }
    }

    public function getToken(): TokenInterface
    {
        if (null !== $this->cachedToken && !$this->cachedToken->isExpired()) {
            return $this->cachedToken;
        }

        $this->cachedToken = $this->requestNewToken();

        return $this->cachedToken;
    }

    private function requestNewToken(): CachedToken
    {
        $now = time();
        $serviceAccount = $this->credentials->getServiceAccountData();

        $clientEmail = $serviceAccount['client_email'] ?? null;
        $privateKey = $serviceAccount['private_key'] ?? null;

        if (!\is_string($clientEmail) || !\is_string($privateKey)) {
            throw new AuthenticationException('Service account JSON must contain "client_email" and "private_key" fields.');
        }

        // Build JWT assertion
        $header = self::base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT'], \JSON_THROW_ON_ERROR));
        $claims = self::base64UrlEncode(json_encode([
            'iss' => $clientEmail,
            'scope' => self::SCOPE,
            'aud' => self::TOKEN_URL,
            'iat' => $now,
            'exp' => $now + 3600,
        ], \JSON_THROW_ON_ERROR));

        $signingInput = $header . '.' . $claims;

        $key = openssl_pkey_get_private($privateKey);

        if (false === $key) {
            throw new AuthenticationException('Unable to read private key from Google service account.');
        }

        $signature = '';

        if (!openssl_sign($signingInput, $signature, $key, \OPENSSL_ALGO_SHA256)) {
            throw new AuthenticationException(\sprintf('Failed to sign JWT: %s', openssl_error_string() ?: 'unknown error'));
        }

        $jwt = $signingInput . '.' . self::base64UrlEncode($signature);

        // Exchange JWT for access token
        try {
            $response = $this->httpClient->request('POST', self::TOKEN_URL, [
                'body' => [
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion' => $jwt,
                ],
            ]);

            $statusCode = $response->getStatusCode();
            $content = $response->getContent(false);
        } catch (TransportExceptionInterface $e) {
            throw new HttpRequestException(\sprintf('Google OAuth2 token request transport failure: %s', $e->getMessage()), $e);
        }

        if ($statusCode < 200 || $statusCode >= 300) {
            throw new AuthenticationException(\sprintf('Google OAuth2 token request failed with status %d: %s', $statusCode, $content));
        }

        $data = json_decode($content, true);

        if (!\is_array($data)) {
            throw new AuthenticationException(\sprintf('Google OAuth2 token endpoint returned non-JSON body (HTTP %d).', $statusCode));
        }

        $accessToken = \array_key_exists('access_token', $data) ? $data['access_token'] : null;
        $expiresIn = \array_key_exists('expires_in', $data) ? $data['expires_in'] : null;

        if (!\is_string($accessToken) || '' === $accessToken) {
            throw new AuthenticationException(\sprintf('Google OAuth2 token response is missing a string "access_token": %s', json_encode($data, \JSON_THROW_ON_ERROR)));
        }

        if (!\is_int($expiresIn) || $expiresIn < 60) {
            throw new AuthenticationException(\sprintf('Google OAuth2 token response has an invalid "expires_in" (%s).', \get_debug_type($expiresIn)));
        }

        return new CachedToken(
            $accessToken,
            new \DateTimeImmutable(\sprintf('+%d seconds', \max(60, $expiresIn - 60))),
        );
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
