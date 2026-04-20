<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Api\Apple;

use Jolicode\WalletKit\Api\Auth\AppleApnsJwtProvider;
use Jolicode\WalletKit\Exception\Api\HttpRequestException;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class ApplePushNotifier
{
    private const PRODUCTION_HOST = 'https://api.push.apple.com';
    private const SANDBOX_HOST = 'https://api.sandbox.push.apple.com';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly AppleApnsJwtProvider $jwtProvider,
        private readonly bool $sandbox = false,
    ) {
    }

    public function sendUpdateNotification(string $pushToken, string $passTypeId): ApnsPushResponse
    {
        $response = $this->sendRequest($pushToken, $passTypeId);

        return $this->buildResponse($pushToken, $response);
    }

    /**
     * @param string[] $pushTokens
     *
     * @return ApnsPushResponse[]
     */
    public function sendBatchUpdateNotifications(array $pushTokens, string $passTypeId): array
    {
        if ([] === $pushTokens) {
            return [];
        }

        // Fire all requests concurrently (HttpClient handles HTTP/2 multiplexing)
        /** @var array<string, ResponseInterface> $responses token => response */
        $responses = [];
        foreach ($pushTokens as $pushToken) {
            $responses[$pushToken] = $this->sendRequest($pushToken, $passTypeId);
        }

        // Collect all responses
        $results = [];
        foreach ($responses as $pushToken => $response) {
            $results[] = $this->buildResponse($pushToken, $response);
        }

        return $results;
    }

    private function sendRequest(string $pushToken, string $passTypeId): ResponseInterface
    {
        $host = $this->sandbox ? self::SANDBOX_HOST : self::PRODUCTION_HOST;
        $url = $host . '/3/device/' . $pushToken;
        $jwt = $this->jwtProvider->getToken()->getAccessToken();

        try {
            return $this->httpClient->request('POST', $url, [
                'headers' => [
                    'authorization' => 'bearer ' . $jwt,
                    'apns-topic' => $passTypeId,
                    'apns-push-type' => 'background',
                ],
                'body' => '{}',
            ]);
        } catch (TransportExceptionInterface $e) {
            throw new HttpRequestException(\sprintf('APNS push request failed for token "%s": %s', $pushToken, $e->getMessage()), $e);
        }
    }

    private function buildResponse(string $pushToken, ResponseInterface $response): ApnsPushResponse
    {
        try {
            $statusCode = $response->getStatusCode();
            $headers = $response->getHeaders(false);
            $content = $response->getContent(false);
        } catch (TransportExceptionInterface $e) {
            throw new HttpRequestException(\sprintf('APNS push response failed for token "%s": %s', $pushToken, $e->getMessage()), $e);
        }

        $apnsId = $headers['apns-id'][0] ?? null;
        $errorReason = null;

        if (200 !== $statusCode && '' !== $content) {
            /** @var array{reason?: string} $body */
            $body = json_decode($content, true, 512, \JSON_THROW_ON_ERROR);
            $errorReason = $body['reason'] ?? null;
        }

        return new ApnsPushResponse($pushToken, $statusCode, $errorReason, $apnsId);
    }
}
