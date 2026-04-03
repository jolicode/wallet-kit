<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Normalizer\Flight;

use Jolicode\WalletKit\Pass\Android\Model\Flight\AirportInfo;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type AirportInfoType from AirportInfo
 */
class AirportInfoNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @param AirportInfo          $object
     * @param array<string, mixed> $context
     *
     * @return AirportInfoType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [];

        if (null !== $object->airportIataCode) {
            $data['airportIataCode'] = $object->airportIataCode;
        }

        if (null !== $object->airportNameOverride) {
            $data['airportNameOverride'] = $this->normalizer->normalize($object->airportNameOverride, $format, $context);
        }

        if (null !== $object->terminal) {
            $data['terminal'] = $object->terminal;
        }

        if (null !== $object->gate) {
            $data['gate'] = $object->gate;
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof AirportInfo;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [AirportInfo::class => true];
    }
}
