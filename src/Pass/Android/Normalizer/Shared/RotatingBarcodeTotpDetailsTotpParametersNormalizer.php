<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Normalizer\Shared;

use Jolicode\WalletKit\Pass\Android\Model\Shared\RotatingBarcodeTotpDetailsTotpParameters;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type RotatingBarcodeTotpDetailsTotpParametersType from RotatingBarcodeTotpDetailsTotpParameters
 */
class RotatingBarcodeTotpDetailsTotpParametersNormalizer implements NormalizerInterface
{
    /**
     * @param RotatingBarcodeTotpDetailsTotpParameters $object
     * @param array<string, mixed>                     $context
     *
     * @return RotatingBarcodeTotpDetailsTotpParametersType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [];

        if (null !== $object->key) {
            $data['key'] = $object->key;
        }

        if (null !== $object->valueLength) {
            $data['valueLength'] = $object->valueLength;
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof RotatingBarcodeTotpDetailsTotpParameters;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [RotatingBarcodeTotpDetailsTotpParameters::class => true];
    }
}
