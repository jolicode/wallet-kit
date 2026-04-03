<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Apple\Normalizer\SemanticTagType;

use Jolicode\WalletKit\Pass\Apple\Model\SemanticTagType\WifiNetwork;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type WifiNetworkType from WifiNetwork
 */
class WifiNetworkNormalizer implements NormalizerInterface
{
    /**
     * @param WifiNetwork          $object
     * @param array<string, mixed> $context
     *
     * @return WifiNetworkType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [
            'ssid' => $object->ssid,
        ];

        if (null !== $object->password) {
            $data['password'] = $object->password;
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof WifiNetwork;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [WifiNetwork::class => true];
    }
}
