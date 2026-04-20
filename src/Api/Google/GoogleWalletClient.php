<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Api\Google;

use Jolicode\WalletKit\Api\Auth\GoogleOAuth2Authenticator;
use Jolicode\WalletKit\Builder\GoogleVerticalEnum;
use Jolicode\WalletKit\Builder\GoogleWalletPair;
use Jolicode\WalletKit\Exception\Api\ApiResponseException;
use Jolicode\WalletKit\Exception\Api\HttpRequestException;
use Jolicode\WalletKit\Exception\Api\RateLimitException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class GoogleWalletClient
{
    private const BASE_URL = 'https://walletobjects.googleapis.com/walletobjects/v1/';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly NormalizerInterface $normalizer,
        private readonly GoogleOAuth2Authenticator $authenticator,
    ) {
    }

    public function createClass(GoogleWalletPair $pair): GoogleApiResponse
    {
        $url = self::BASE_URL . self::classSegment($pair->vertical);
        $body = $this->normalizer->normalize($pair->issuerClass);

        return $this->request('POST', $url, $body);
    }

    public function getClass(GoogleVerticalEnum $vertical, string $classId): GoogleApiResponse
    {
        $url = self::BASE_URL . self::classSegment($vertical) . '/' . $classId;

        return $this->request('GET', $url);
    }

    public function updateClass(GoogleWalletPair $pair): GoogleApiResponse
    {
        $url = self::BASE_URL . self::classSegment($pair->vertical) . '/' . $pair->issuerClass->id;
        $body = $this->normalizer->normalize($pair->issuerClass);

        return $this->request('PUT', $url, $body);
    }

    public function patchClass(GoogleWalletPair $pair): GoogleApiResponse
    {
        $url = self::BASE_URL . self::classSegment($pair->vertical) . '/' . $pair->issuerClass->id;
        $body = $this->normalizer->normalize($pair->issuerClass);

        return $this->request('PATCH', $url, $body);
    }

    public function createObject(GoogleWalletPair $pair): GoogleApiResponse
    {
        $url = self::BASE_URL . self::objectSegment($pair->vertical);
        $body = $this->normalizer->normalize($pair->passObject);

        return $this->request('POST', $url, $body);
    }

    public function getObject(GoogleVerticalEnum $vertical, string $objectId): GoogleApiResponse
    {
        $url = self::BASE_URL . self::objectSegment($vertical) . '/' . $objectId;

        return $this->request('GET', $url);
    }

    public function updateObject(GoogleWalletPair $pair): GoogleApiResponse
    {
        $url = self::BASE_URL . self::objectSegment($pair->vertical) . '/' . $pair->passObject->id;
        $body = $this->normalizer->normalize($pair->passObject);

        return $this->request('PUT', $url, $body);
    }

    public function patchObject(GoogleWalletPair $pair): GoogleApiResponse
    {
        $url = self::BASE_URL . self::objectSegment($pair->vertical) . '/' . $pair->passObject->id;
        $body = $this->normalizer->normalize($pair->passObject);

        return $this->request('PATCH', $url, $body);
    }

    /**
     * Convenience: creates class (ignoring 409 Conflict), then creates or updates object.
     */
    public function createOrUpdatePass(GoogleWalletPair $pair): void
    {
        $classResponse = $this->createClass($pair);
        if (409 === $classResponse->getStatusCode()) {
            $classResponse = $this->updateClass($pair);
        }
        if (!$classResponse->isSuccessful()) {
            throw new ApiResponseException($classResponse->getStatusCode(), $classResponse->getRawBody(), \sprintf('Failed to create or update class for vertical "%s".', $pair->vertical->value));
        }

        $objectResponse = $this->createObject($pair);
        if (409 === $objectResponse->getStatusCode()) {
            $objectResponse = $this->updateObject($pair);
        }
        if (!$objectResponse->isSuccessful()) {
            throw new ApiResponseException($objectResponse->getStatusCode(), $objectResponse->getRawBody(), \sprintf('Failed to create or update object for vertical "%s".', $pair->vertical->value));
        }
    }

    /**
     * @param array<string, mixed>|null $body
     */
    private function request(string $method, string $url, ?array $body = null): GoogleApiResponse
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
            throw new HttpRequestException(\sprintf('Google Wallet API request failed: %s', $e->getMessage()), $e);
        }

        $decoded = '' !== $content ? json_decode($content, true, 512, \JSON_THROW_ON_ERROR) : [];
        /** @var array<string, mixed> $data */
        $data = \is_array($decoded) ? $decoded : [];

        if (429 === $statusCode) {
            $retryAfter = $response->getHeaders(false)['retry-after'][0] ?? null;

            throw new RateLimitException($content, null !== $retryAfter ? (int) $retryAfter : null);
        }

        return new GoogleApiResponse($statusCode, $data, $content);
    }

    private static function classSegment(GoogleVerticalEnum $vertical): string
    {
        return self::camelCaseVertical($vertical) . 'Class';
    }

    private static function objectSegment(GoogleVerticalEnum $vertical): string
    {
        return self::camelCaseVertical($vertical) . 'Object';
    }

    private static function camelCaseVertical(GoogleVerticalEnum $vertical): string
    {
        return match ($vertical) {
            GoogleVerticalEnum::FLIGHT => 'flight',
            GoogleVerticalEnum::EVENT_TICKET => 'eventTicket',
            GoogleVerticalEnum::GENERIC => 'generic',
            GoogleVerticalEnum::GIFT_CARD => 'giftCard',
            GoogleVerticalEnum::LOYALTY => 'loyalty',
            GoogleVerticalEnum::OFFER => 'offer',
            GoogleVerticalEnum::TRANSIT => 'transit',
        };
    }
}
