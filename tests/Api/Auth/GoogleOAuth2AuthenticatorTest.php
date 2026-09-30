<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Tests\Api\Auth;

use Jolicode\WalletKit\Api\Auth\GoogleOAuth2Authenticator;
use Jolicode\WalletKit\Api\Credentials\GoogleCredentials;
use Jolicode\WalletKit\Exception\Api\AuthenticationException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class GoogleOAuth2AuthenticatorTest extends TestCase
{
    private string $serviceAccountPath;

    protected function setUp(): void
    {
        // Generate an RSA key pair for the test service account
        $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => \OPENSSL_KEYTYPE_RSA]);
        self::assertNotFalse($key);
        openssl_pkey_export($key, $privatePem);

        $this->serviceAccountPath = tempnam(sys_get_temp_dir(), 'wallet_kit_sa_') ?: '';
        file_put_contents($this->serviceAccountPath, json_encode([
            'type' => 'service_account',
            'client_email' => 'test@test-project.iam.gserviceaccount.com',
            'private_key' => $privatePem,
        ], \JSON_THROW_ON_ERROR));
    }

    protected function tearDown(): void
    {
        @unlink($this->serviceAccountPath);
    }

    public function testGetTokenReturnsValidToken(): void
    {
        $mockResponse = new MockResponse(json_encode([
            'access_token' => 'ya29.test-access-token',
            'expires_in' => 3600,
            'token_type' => 'Bearer',
        ], \JSON_THROW_ON_ERROR));

        $httpClient = new MockHttpClient($mockResponse);
        $credentials = new GoogleCredentials($this->serviceAccountPath);

        $authenticator = new GoogleOAuth2Authenticator($httpClient, $credentials);
        $token = $authenticator->getToken();

        self::assertSame('ya29.test-access-token', $token->getAccessToken());
        self::assertFalse($token->isExpired());
    }

    public function testGetTokenCachesToken(): void
    {
        $callCount = 0;
        $httpClient = new MockHttpClient(function () use (&$callCount) {
            ++$callCount;

            return new MockResponse(json_encode([
                'access_token' => 'ya29.cached-token',
                'expires_in' => 3600,
            ], \JSON_THROW_ON_ERROR));
        });

        $credentials = new GoogleCredentials($this->serviceAccountPath);
        $authenticator = new GoogleOAuth2Authenticator($httpClient, $credentials);

        $token1 = $authenticator->getToken();
        $token2 = $authenticator->getToken();

        self::assertSame($token1->getAccessToken(), $token2->getAccessToken());
        self::assertSame(1, $callCount);
    }

    public function testGetTokenSendsCorrectJwtAssertion(): void
    {
        $lastRequest = null;
        $httpClient = new MockHttpClient(function ($method, $url, $options) use (&$lastRequest) {
            $lastRequest = ['method' => $method, 'url' => $url, 'body' => $options['body'] ?? ''];

            return new MockResponse(json_encode([
                'access_token' => 'ya29.test',
                'expires_in' => 3600,
            ], \JSON_THROW_ON_ERROR));
        });

        $credentials = new GoogleCredentials($this->serviceAccountPath);
        $authenticator = new GoogleOAuth2Authenticator($httpClient, $credentials);
        $authenticator->getToken();

        self::assertNotNull($lastRequest);
        self::assertSame('POST', $lastRequest['method']);
        self::assertSame('https://oauth2.googleapis.com/token', $lastRequest['url']);

        // Parse the form body
        $body = $lastRequest['body'];
        self::assertStringContainsString('grant_type=urn', $body);
        self::assertStringContainsString('assertion=', $body);
    }

    public function testThrowsOnFailedTokenRequest(): void
    {
        $httpClient = new MockHttpClient(new MockResponse('{"error":"invalid_grant"}', [
            'http_code' => 400,
        ]));

        $credentials = new GoogleCredentials($this->serviceAccountPath);
        $authenticator = new GoogleOAuth2Authenticator($httpClient, $credentials);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Google OAuth2 token request failed');
        $authenticator->getToken();
    }

    public function testThrowsOnMissingServiceAccountFields(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'wallet_kit_sa_bad_') ?: '';
        file_put_contents($tmpFile, json_encode(['type' => 'service_account'], \JSON_THROW_ON_ERROR));

        try {
            $httpClient = new MockHttpClient([]);
            $credentials = new GoogleCredentials($tmpFile);
            $authenticator = new GoogleOAuth2Authenticator($httpClient, $credentials);

            $this->expectException(AuthenticationException::class);
            $this->expectExceptionMessage('client_email');
            $authenticator->getToken();
        } finally {
            @unlink($tmpFile);
        }
    }
}
