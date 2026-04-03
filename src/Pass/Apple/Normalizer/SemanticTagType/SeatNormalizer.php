<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Apple\Normalizer\SemanticTagType;

use Jolicode\WalletKit\Pass\Apple\Model\SemanticTagType\Seat;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type SeatType from Seat
 */
class SeatNormalizer implements NormalizerInterface
{
    /**
     * @param Seat                 $object
     * @param array<string, mixed> $context
     *
     * @return SeatType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [];

        if (null !== $object->seatDescription) {
            $data['seatDescription'] = $object->seatDescription;
        }

        if (null !== $object->seatIdentifier) {
            $data['seatIdentifier'] = $object->seatIdentifier;
        }

        if (null !== $object->seatNumber) {
            $data['seatNumber'] = $object->seatNumber;
        }

        if (null !== $object->seatRow) {
            $data['seatRow'] = $object->seatRow;
        }

        if (null !== $object->seatSection) {
            $data['seatSection'] = $object->seatSection;
        }

        if (null !== $object->seatType) {
            $data['seatType'] = $object->seatType;
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Seat;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [Seat::class => true];
    }
}
