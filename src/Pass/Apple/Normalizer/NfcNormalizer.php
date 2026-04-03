<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Apple\Normalizer;

use Jolicode\WalletKit\Pass\Apple\Model\Nfc;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type NfcType from Nfc
 */
class NfcNormalizer implements NormalizerInterface
{
    /**
     * @param Nfc                  $object
     * @param array<string, mixed> $context
     *
     * @return NfcType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [
            'message' => $object->message,
            'encryptionPublicKey' => $object->encryptionPublicKey,
        ];

        if (null !== $object->requiresAuthentication) {
            $data['requiresAuthentication'] = $object->requiresAuthentication;
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Nfc;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [Nfc::class => true];
    }
}
