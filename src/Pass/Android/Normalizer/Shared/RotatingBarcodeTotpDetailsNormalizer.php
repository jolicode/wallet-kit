<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Normalizer\Shared;

use Jolicode\WalletKit\Pass\Android\Model\Shared\RotatingBarcodeTotpDetails;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type RotatingBarcodeTotpDetailsType from RotatingBarcodeTotpDetails
 */
class RotatingBarcodeTotpDetailsNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @param RotatingBarcodeTotpDetails $object
     * @param array<string, mixed>       $context
     *
     * @return RotatingBarcodeTotpDetailsType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [];

        if (null !== $object->totpParameters) {
            $totpParameters = [];
            foreach ($object->totpParameters as $totpParameter) {
                $totpParameters[] = $this->normalizer->normalize($totpParameter, $format, $context);
            }
            $data['totpParameters'] = $totpParameters;
        }

        if (null !== $object->periodMillis) {
            $data['periodMillis'] = $object->periodMillis;
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof RotatingBarcodeTotpDetails;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [RotatingBarcodeTotpDetails::class => true];
    }
}
