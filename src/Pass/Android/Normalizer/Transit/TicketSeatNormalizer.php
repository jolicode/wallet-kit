<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Normalizer\Transit;

use Jolicode\WalletKit\Pass\Android\Model\Transit\TicketSeat;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type TicketSeatType from TicketSeat
 */
class TicketSeatNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @param TicketSeat           $object
     * @param array<string, mixed> $context
     *
     * @return TicketSeatType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [];

        if (null !== $object->fareClass) {
            $data['fareClass'] = $object->fareClass->value;
        }

        if (null !== $object->customFareClass) {
            $data['customFareClass'] = $this->normalizer->normalize($object->customFareClass, $format, $context);
        }

        if (null !== $object->coach) {
            $data['coach'] = $object->coach;
        }

        if (null !== $object->seat) {
            $data['seat'] = $object->seat;
        }

        if (null !== $object->seatAssignment) {
            $data['seatAssignment'] = $this->normalizer->normalize($object->seatAssignment, $format, $context);
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof TicketSeat;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [TicketSeat::class => true];
    }
}
