<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Api\Samsung;

use Jolicode\WalletKit\Api\Auth\SamsungJwtAuthenticator;
use Jolicode\WalletKit\Api\Credentials\SamsungCredentials;
use Jolicode\WalletKit\Exception\Api\HttpRequestException;
use Jolicode\WalletKit\Exception\Api\RateLimitException;
use Jolicode\WalletKit\Pass\Samsung\Model\Card;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class SamsungWalletClient
{
    private readonly string $baseUrl;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly NormalizerInterface $normalizer,
        private readonly SamsungJwtAuthenticator $authenticator,
        SamsungCredentials $credentials,
    ) {
        $this->baseUrl = $credentials->region->getBaseUrl();
    }

    public function createCard(Card $card): SamsungApiResponse
    {
        $body = $this->normalizer->normalize($card);

        return $this->request('POST', $this->baseUrl . 'cards', $body);
    }

    public function getCard(string $cardId): SamsungApiResponse
    {
        return $this->request('GET', $this->baseUrl . 'cards/' . $cardId);
    }

    public function updateCard(Card $card, string $cardId): SamsungApiResponse
    {
        $body = $this->normalizer->normalize($card);

        return $this->request('PUT', $this->baseUrl . 'cards/' . $cardId, $body);
    }

    public function updateCardState(string $cardId, string $state): SamsungApiResponse
    {
        return $this->request('PATCH', $this->baseUrl . 'cards/' . $cardId, [
            'state' => $state,
        ]);
    }

    /**
     * Send push notification to update card on user's device.
     * Samsung handles push delivery when cards are updated via the Partner API,
     * but explicit push can be triggered for state changes.
     */
    public function pushCardUpdate(string $cardId): SamsungApiResponse
    {
        return $this->request('POST', $this->baseUrl . 'cards/' . $cardId . '/push');
    }

    /**
     * @param array<string, mixed>|null $body
     */
    private function request(string $method, string $url, ?array $body = null): SamsungApiResponse
    {
        $token = $this->authenticator->getToken();

        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . $token->getAccessToken(),
                'Content-Type' => 'application/json',
            ],
        ];

        if (null !== $body) {
            $options['json'] = $body;
        }

        try {
            $response = $this->httpClient->request($method, $url, $options);
            $statusCode = $response->getStatusCode();
            $content = $response->getContent(false);
        } catch (TransportExceptionInterface $e) {
            throw new HttpRequestException(\sprintf('Samsung Wallet API request failed: %s', $e->getMessage()), $e);
        }

        $decoded = '' !== $content ? json_decode($content, true, 512, \JSON_THROW_ON_ERROR) : [];
        /** @var array<string, mixed> $data */
        $data = \is_array($decoded) ? $decoded : [];

        if (429 === $statusCode) {
            $retryAfter = $response->getHeaders(false)['retry-after'][0] ?? null;

            throw new RateLimitException($content, null !== $retryAfter ? (int) $retryAfter : null);
        }

        return new SamsungApiResponse($statusCode, $data, $content);
    }
}
