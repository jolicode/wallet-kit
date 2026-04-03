<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Normalizer\Flight;

use Jolicode\WalletKit\Pass\Android\Model\Flight\FlightHeader;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type FlightHeaderType from FlightHeader
 */
class FlightHeaderNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @param FlightHeader         $object
     * @param array<string, mixed> $context
     *
     * @return FlightHeaderType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [];

        if (null !== $object->carrier) {
            $data['carrier'] = $this->normalizer->normalize($object->carrier, $format, $context);
        }

        if (null !== $object->flightNumber) {
            $data['flightNumber'] = $object->flightNumber;
        }

        if (null !== $object->operatingCarrier) {
            $data['operatingCarrier'] = $this->normalizer->normalize($object->operatingCarrier, $format, $context);
        }

        if (null !== $object->operatingFlightNumber) {
            $data['operatingFlightNumber'] = $object->operatingFlightNumber;
        }

        if (null !== $object->flightNumberDisplayOverride) {
            $data['flightNumberDisplayOverride'] = $object->flightNumberDisplayOverride;
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof FlightHeader;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [FlightHeader::class => true];
    }
}
