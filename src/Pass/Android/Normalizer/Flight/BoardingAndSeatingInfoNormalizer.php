<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Normalizer\Flight;

use Jolicode\WalletKit\Pass\Android\Model\Flight\BoardingAndSeatingInfo;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type BoardingAndSeatingInfoType from BoardingAndSeatingInfo
 */
class BoardingAndSeatingInfoNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @param BoardingAndSeatingInfo $object
     * @param array<string, mixed>   $context
     *
     * @return BoardingAndSeatingInfoType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [];

        if (null !== $object->boardingGroup) {
            $data['boardingGroup'] = $object->boardingGroup;
        }

        if (null !== $object->seatNumber) {
            $data['seatNumber'] = $object->seatNumber;
        }

        if (null !== $object->seatClass) {
            $data['seatClass'] = $object->seatClass;
        }

        if (null !== $object->boardingPrivilegeImage) {
            $data['boardingPrivilegeImage'] = $this->normalizer->normalize($object->boardingPrivilegeImage, $format, $context);
        }

        if (null !== $object->boardingPosition) {
            $data['boardingPosition'] = $object->boardingPosition;
        }

        if (null !== $object->sequenceNumber) {
            $data['sequenceNumber'] = $object->sequenceNumber;
        }

        if (null !== $object->boardingDoor) {
            $data['boardingDoor'] = $object->boardingDoor->value;
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
        return $data instanceof BoardingAndSeatingInfo;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [BoardingAndSeatingInfo::class => true];
    }
}
