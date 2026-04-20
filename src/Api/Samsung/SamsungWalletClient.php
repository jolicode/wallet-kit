<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Api\Samsung;

use Jolicode\WalletKit\Api\Auth\SamsungJwtAuthenticator;
use Jolicode\WalletKit\Exception\Api\HttpRequestException;
use Jolicode\WalletKit\Exception\Api\RateLimitException;
use Jolicode\WalletKit\Pass\Samsung\Model\Card;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class SamsungWalletClient
{
    private const BASE_URL = 'https://api-us1.mpay.samsung.com/wallet/v2.1/';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly NormalizerInterface $normalizer,
        private readonly SamsungJwtAuthenticator $authenticator,
    ) {
    }

    public function createCard(Card $card): SamsungApiResponse
    {
        $body = $this->normalizer->normalize($card);

        return $this->request('POST', self::BASE_URL . 'cards', $body);
    }

    public function getCard(string $cardId): SamsungApiResponse
    {
        return $this->request('GET', self::BASE_URL . 'cards/' . $cardId);
    }

    public function updateCard(Card $card, string $cardId): SamsungApiResponse
    {
        $body = $this->normalizer->normalize($card);

        return $this->request('PUT', self::BASE_URL . 'cards/' . $cardId, $body);
    }

    public function updateCardState(string $cardId, string $state): SamsungApiResponse
    {
        return $this->request('PATCH', self::BASE_URL . 'cards/' . $cardId, [
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
        return $this->request('POST', self::BASE_URL . 'cards/' . $cardId . '/push');
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

        /** @var array<string, mixed> $data */
        $data = '' !== $content ? (array) json_decode($content, true, 512, \JSON_THROW_ON_ERROR) : [];

        if (429 === $statusCode) {
            $retryAfter = $response->getHeaders(false)['retry-after'][0] ?? null;

            throw new RateLimitException($content, null !== $retryAfter ? (int) $retryAfter : null);
        }

        return new SamsungApiResponse($statusCode, $data);
    }
}
