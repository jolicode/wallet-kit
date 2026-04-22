<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Api\Google;

use Jolicode\WalletKit\Api\Credentials\GoogleCredentials;
use Jolicode\WalletKit\Builder\GoogleVerticalEnum;
use Jolicode\WalletKit\Builder\GoogleWalletPair;
use Jolicode\WalletKit\Exception\Api\AuthenticationException;
use Jolicode\WalletKit\Exception\Api\MissingExtensionException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class GoogleSaveLinkGenerator
{
    public function __construct(
        private readonly NormalizerInterface $normalizer,
        private readonly GoogleCredentials $credentials,
    ) {
        if (!\extension_loaded('openssl')) {
            throw new MissingExtensionException('The "openssl" PHP extension is required for Google Save Link generation.');
        }
    }

    /**
     * @return string https://pay.google.com/gp/v/save/{jwt}
     */
    public function generateSaveLink(GoogleWalletPair $pair): string
    {
        $serviceAccount = $this->credentials->getServiceAccountData();

        $clientEmail = $serviceAccount['client_email'] ?? null;
        $privateKey = $serviceAccount['private_key'] ?? null;

        if (!\is_string($clientEmail) || !\is_string($privateKey)) {
            throw new AuthenticationException('Service account JSON must contain "client_email" and "private_key" fields.');
        }

        $normalizedClass = $this->normalizer->normalize($pair->issuerClass);
        $normalizedObject = $this->normalizer->normalize($pair->passObject);
        $classesKey = self::classesPayloadKey($pair->vertical);
        $objectsKey = self::objectsPayloadKey($pair->vertical);

        $header = self::base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT'], \JSON_THROW_ON_ERROR));
        $claims = self::base64UrlEncode(json_encode([
            'iss' => $clientEmail,
            'aud' => 'google',
            'typ' => 'savetowallet',
            'payload' => [
                $classesKey => [$normalizedClass],
                $objectsKey => [$normalizedObject],
            ],
        ], \JSON_THROW_ON_ERROR));

        $signingInput = $header . '.' . $claims;

        $key = openssl_pkey_get_private($privateKey);

        if (false === $key) {
            throw new AuthenticationException('Unable to read private key from Google service account.');
        }

        $signature = '';

        if (!openssl_sign($signingInput, $signature, $key, \OPENSSL_ALGO_SHA256)) {
            throw new AuthenticationException(\sprintf('Failed to sign save link JWT: %s', openssl_error_string() ?: 'unknown error'));
        }

        $jwt = $signingInput . '.' . self::base64UrlEncode($signature);

        return 'https://pay.google.com/gp/v/save/' . $jwt;
    }

    private static function objectsPayloadKey(GoogleVerticalEnum $vertical): string
    {
        return match ($vertical) {
            GoogleVerticalEnum::FLIGHT => 'flightObjects',
            GoogleVerticalEnum::EVENT_TICKET => 'eventTicketObjects',
            GoogleVerticalEnum::GENERIC => 'genericObjects',
            GoogleVerticalEnum::GIFT_CARD => 'giftCardObjects',
            GoogleVerticalEnum::LOYALTY => 'loyaltyObjects',
            GoogleVerticalEnum::OFFER => 'offerObjects',
            GoogleVerticalEnum::TRANSIT => 'transitObjects',
        };
    }

    private static function classesPayloadKey(GoogleVerticalEnum $vertical): string
    {
        return match ($vertical) {
            GoogleVerticalEnum::FLIGHT => 'flightClasses',
            GoogleVerticalEnum::EVENT_TICKET => 'eventTicketClasses',
            GoogleVerticalEnum::GENERIC => 'genericClasses',
            GoogleVerticalEnum::GIFT_CARD => 'giftCardClasses',
            GoogleVerticalEnum::LOYALTY => 'loyaltyClasses',
            GoogleVerticalEnum::OFFER => 'offerClasses',
            GoogleVerticalEnum::TRANSIT => 'transitClasses',
        };
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
