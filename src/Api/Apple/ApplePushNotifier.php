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
        return $this->readResponse($pushToken, $passTypeId, $this->sendRequest($pushToken, $passTypeId));
    }

    /**
     * @param string[] $pushTokens
     *
     * @return ApnsPushResponse[] one response per input token, input order preserved
     */
    public function sendBatchUpdateNotifications(array $pushTokens, string $passTypeId): array
    {
        if ([] === $pushTokens) {
            return [];
        }

        // Fire all requests lazily, then read every response: a single transport
        // failure must not sink the whole batch — it records an failed response
        // for that token only.
        /** @var list<ResponseInterface> $responses */
        $responses = [];
        foreach ($pushTokens as $pushToken) {
            $responses[] = $this->sendRequest($pushToken, $passTypeId);
        }

        $results = [];
        foreach ($pushTokens as $i => $pushToken) {
            $results[] = $this->readResponse($pushToken, $passTypeId, $responses[$i]);
        }

        return $results;
    }

    /**
     * Reads the response and transparently handles the one recoverable APNs
     * failure: an expired provider token — the JWT is re-minted, then the
     * push is retried once.
     */
    private function readResponse(string $pushToken, string $passTypeId, ResponseInterface $response): ApnsPushResponse
    {
        try {
            $result = $this->buildResponse($pushToken, $response);
        } catch (TransportExceptionInterface $e) {
            return ApnsPushResponse::transportFailure($pushToken, $e->getMessage());
        }

        if (403 === $result->getStatusCode() && 'ExpiredProviderToken' === $result->getErrorReason()) {
            $this->jwtProvider->invalidate();

            $retry = $this->buildResponse($pushToken, $this->sendRequest($pushToken, $passTypeId));

            if (!($retry->isFailed() && 'ExpiredProviderToken' === $retry->getErrorReason())) {
                return $retry;
            }
        }

        return $result;
    }

    private function sendRequest(string $pushToken, string $passTypeId): ResponseInterface
    {
        $host = $this->sandbox ? self::SANDBOX_HOST : self::PRODUCTION_HOST;
        $url = $host . '/3/device/' . $pushToken;

        try {
            return $this->httpClient->request('POST', $url, [
                'headers' => $this->buildHeaders($passTypeId),
                'body' => '{}',
            ]);
        } catch (TransportExceptionInterface $e) {
            throw new HttpRequestException(\sprintf('APNS push request failed for token "%s…": %s', substr($pushToken, 0, 6), $e->getMessage()), $e);
        }
    }

    /**
     * @return array<string, string>
     */
    private function buildHeaders(string $passTypeId): array
    {
        $jwt = $this->jwtProvider->getToken()->getAccessToken();

        return [
            'authorization' => 'bearer ' . $jwt,
            'apns-topic' => $passTypeId,
            'apns-push-type' => 'background',
            // Apple rejects background pushes sent with the default priority 10.
            'apns-priority' => '5',
        ];
    }

    private function buildResponse(string $pushToken, ResponseInterface $response): ApnsPushResponse
    {
        try {
            $statusCode = $response->getStatusCode();
            $headers = $response->getHeaders(false);
            $content = $response->getContent(false);
        } catch (TransportExceptionInterface $e) {
            throw new HttpRequestException(\sprintf('APNS push response failed for token "%s…": %s', substr($pushToken, 0, 6), $e->getMessage()), $e);
        }

        $apnsId = $headers['apns-id'][0] ?? null;
        $errorReason = null;

        if (200 !== $statusCode && '' !== $content) {
            // Error bodies are documented JSON, but proxies may answer HTML etc.
            $decoded = json_decode($content, true);

            if (\is_array($decoded) && \array_key_exists('reason', $decoded) && \is_string($decoded['reason'])) {
                $errorReason = $decoded['reason'];
            }
        }

        return new ApnsPushResponse($pushToken, $statusCode, $errorReason, $apnsId);
    }
}
