<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Normalizer\Transit;

use Jolicode\WalletKit\Pass\Android\Model\Transit\TicketLeg;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type TicketLegType from TicketLeg
 */
class TicketLegNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @param TicketLeg            $object
     * @param array<string, mixed> $context
     *
     * @return TicketLegType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [];

        if (null !== $object->originStationCode) {
            $data['originStationCode'] = $object->originStationCode;
        }

        if (null !== $object->originName) {
            $data['originName'] = $this->normalizer->normalize($object->originName, $format, $context);
        }

        if (null !== $object->destinationStationCode) {
            $data['destinationStationCode'] = $object->destinationStationCode;
        }

        if (null !== $object->destinationName) {
            $data['destinationName'] = $this->normalizer->normalize($object->destinationName, $format, $context);
        }

        if (null !== $object->departureDateTime) {
            $data['departureDateTime'] = $object->departureDateTime;
        }

        if (null !== $object->arrivalDateTime) {
            $data['arrivalDateTime'] = $object->arrivalDateTime;
        }

        if (null !== $object->fareName) {
            $data['fareName'] = $this->normalizer->normalize($object->fareName, $format, $context);
        }

        if (null !== $object->carriage) {
            $data['carriage'] = $object->carriage;
        }

        if (null !== $object->platform) {
            $data['platform'] = $object->platform;
        }

        if (null !== $object->zone) {
            $data['zone'] = $object->zone;
        }

        if (null !== $object->ticketSeat) {
            $data['ticketSeat'] = $this->normalizer->normalize($object->ticketSeat, $format, $context);
        }

        if (null !== $object->ticketSeats) {
            $normalized = [];
            foreach ($object->ticketSeats as $ticketSeat) {
                $normalized[] = $this->normalizer->normalize($ticketSeat, $format, $context);
            }
            $data['ticketSeats'] = $normalized;
        }

        if (null !== $object->transitOperatorName) {
            $data['transitOperatorName'] = $this->normalizer->normalize($object->transitOperatorName, $format, $context);
        }

        if (null !== $object->transitTerminusName) {
            $data['transitTerminusName'] = $this->normalizer->normalize($object->transitTerminusName, $format, $context);
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof TicketLeg;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [TicketLeg::class => true];
    }
}
