<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Normalizer\Flight;

use Jolicode\WalletKit\Pass\Android\Model\Flight\FlightCarrier;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type FlightCarrierType from FlightCarrier
 */
class FlightCarrierNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @param FlightCarrier        $object
     * @param array<string, mixed> $context
     *
     * @return FlightCarrierType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [];

        if (null !== $object->carrierIataCode) {
            $data['carrierIataCode'] = $object->carrierIataCode;
        }

        if (null !== $object->airlineName) {
            $data['airlineName'] = $this->normalizer->normalize($object->airlineName, $format, $context);
        }

        if (null !== $object->airlineLogo) {
            $data['airlineLogo'] = $this->normalizer->normalize($object->airlineLogo, $format, $context);
        }

        if (null !== $object->airlineAllianceLogo) {
            $data['airlineAllianceLogo'] = $this->normalizer->normalize($object->airlineAllianceLogo, $format, $context);
        }

        if (null !== $object->wideAirlineLogo) {
            $data['wideAirlineLogo'] = $this->normalizer->normalize($object->wideAirlineLogo, $format, $context);
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof FlightCarrier;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [FlightCarrier::class => true];
    }
}
