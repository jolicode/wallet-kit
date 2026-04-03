<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Normalizer\Flight;

use Jolicode\WalletKit\Pass\Android\Model\Flight\FrequentFlyerInfo;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type FrequentFlyerInfoType from FrequentFlyerInfo
 */
class FrequentFlyerInfoNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @param FrequentFlyerInfo    $object
     * @param array<string, mixed> $context
     *
     * @return FrequentFlyerInfoType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [];

        if (null !== $object->frequentFlyerProgramName) {
            $data['frequentFlyerProgramName'] = $this->normalizer->normalize($object->frequentFlyerProgramName, $format, $context);
        }

        if (null !== $object->frequentFlyerNumber) {
            $data['frequentFlyerNumber'] = $object->frequentFlyerNumber;
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof FrequentFlyerInfo;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [FrequentFlyerInfo::class => true];
    }
}
