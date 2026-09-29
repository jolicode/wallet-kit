<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Api\Samsung;

use Jolicode\WalletKit\Api\Auth\SamsungRequestAuthenticator;
use Jolicode\WalletKit\Api\Credentials\SamsungCredentials;
use Jolicode\WalletKit\Exception\Api\HttpRequestException;
use Jolicode\WalletKit\Exception\Api\RateLimitException;
use Jolicode\WalletKit\Pass\Samsung\Model\Card;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Samsung Wallet Partner API client.
 *
 * Endpoints follow the public docs (developer.samsung.com/wallet). Every call carries:
 * - Authorization: a fresh, per-request JWS (bound to the API method + path);
 * - x-smcs-partner-id: the partner identifier;
 * - x-request-id: a random per-request identifier (<= 32 chars).
 *
 * @see https://developer.samsung.com/wallet/addtosamsungwallet/apiguidelines.html
 */
final class SamsungWalletClient
{
    private readonly SamsungCardTokenizer $cardTokenizer;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly NormalizerInterface $normalizer,
        private readonly SamsungRequestAuthenticator $authenticator,
        private readonly SamsungCredentials $credentials,
    ) {
        $this->cardTokenizer = new SamsungCardTokenizer($credentials);
    }

    /**
     * Create a card template: POST /partner/v1/card/template with "ctemplate".
     */
    public function createCard(Card $card): SamsungApiResponse
    {
        $body = $this->normalizer->normalize($card);

        return $this->request('POST', '/partner/v1/card/template', 'CARD', 'ctemplate', ['card' => $body]);
    }

    /**
     * Update a card template: POST /partner/v1/card/template/{cardId} with "ctemplate".
     */
    public function updateCard(Card $card, string $cardId): SamsungApiResponse
    {
        $body = $this->normalizer->normalize($card);

        return $this->request('POST', \sprintf('/partner/v1/card/template/%s', $cardId), 'CARD', 'ctemplate', ['card' => $body]);
    }

    /**
     * Notify Samsung that card data changed for registered users, so devices pull fresh
     * card data from the partner server: POST /wltex/cards/{cardId}?eventId=…&type=….
     */
    public function pushCardUpdate(string $cardId, string $eventId, string $type): SamsungApiResponse
    {
        // The Authorization path binds to the path only (query excluded per the token spec).
        $pathWithoutQuery = \sprintf('/wltex/cards/%s', $cardId);
        $token = $this->authenticator->createAuthorizationToken('POST', $pathWithoutQuery);

        return $this->doRequest(
            'POST',
            $this->credentials->region->getBaseUrl() . $pathWithoutQuery . '?' . \http_build_query(['eventId' => $eventId, 'type' => $type]),
            $token,
            extraHeaders: [
                'x-smcs-cc2' => $this->credentials->region->getCountryCode(),
            ],
        );
    }

    /**
     * Server-initiated "Add to Samsung Wallet": POST /atw/v1/cards/{cardId} with "cdata".
     */
    public function addCardForUser(string $cardId, array $cardData, ?array $account = null): SamsungApiResponse
    {
        $path = \sprintf('/atw/v1/cards/%s', $cardId);

        $cdata = [
            'card' => $cardData,
        ];

        if (null !== $account) {
            $cdata['account'] = $account;
        }

        return $this->request('POST', $path, 'CARD', 'cdata', $cdata);
    }

    /**
     * Builds a security token around $payload and sends the request.
     *
     * @param array<string, mixed> $payload
     */
    private function request(
        string $method,
        string $path,
        string $contentType,
        string $bodyKey,
        array $payload,
    ): SamsungApiResponse {
        $token = $this->authenticator->createAuthorizationToken($method, $path);
        $body = [
            $bodyKey => $this->cardTokenizer->tokenize($contentType, $payload),
        ];

        return $this->doRequest(
            $method,
            $this->credentials->region->getBaseUrl() . $path,
            $token,
            bodyKeys: $body,
        );
    }

    /**
     * @param array<string, mixed>|null $bodyKeys
     * @param array<string, string>     $extraHeaders
     */
    private function doRequest(string $method, string $url, string $authorizationToken, ?array $bodyKeys = null, array $extraHeaders = []): SamsungApiResponse
    {
        $options = [
            'headers' => $extraHeaders + [
                'Authorization' => 'Bearer ' . $authorizationToken,
                'Content-Type' => 'application/json',
                'x-smcs-partner-id' => $this->credentials->partnerId,
                'x-request-id' => bin2hex(random_bytes(12)), // identifier per API docs, <= 32 chars
            ],
        ];

        if (null !== $bodyKeys) {
            $options['json'] = $bodyKeys;
        }

        try {
            $response = $this->httpClient->request($method, $url, $options);
            $statusCode = $response->getStatusCode();
            $content = $response->getContent(false);
        } catch (TransportExceptionInterface $e) {
            throw new HttpRequestException(\sprintf('Samsung Wallet API request failed: %s', $e->getMessage()), $e);
        }

        if (429 === $statusCode) {
            $retryAfter = $response->getHeaders(false)['retry-after'][0] ?? null;

            throw new RateLimitException($content, \ctype_digit((string) $retryAfter) ? (int) $retryAfter : null);
        }

        // Some endpoints answer with an empty body on 2xx — tolerate that, and wrap
        // malformed bodies (HTML proxies…) instead of leaking a raw \JsonException.
        try {
            $decoded = '' !== $content ? json_decode($content, true, 512, \JSON_THROW_ON_ERROR) : [];
        } catch (\JsonException $e) {
            throw new HttpRequestException(\sprintf('Samsung Wallet API returned non-JSON body (HTTP %d): %s', $statusCode, $e->getMessage()), $e);
        }
        /** @var array<string, mixed> $data */
        $data = \is_array($decoded) ? $decoded : [];

        return new SamsungApiResponse($statusCode, $data, $content);
    }
}
