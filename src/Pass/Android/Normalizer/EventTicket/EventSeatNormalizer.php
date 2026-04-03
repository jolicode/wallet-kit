<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Normalizer\EventTicket;

use Jolicode\WalletKit\Pass\Android\Model\EventTicket\EventSeat;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type EventSeatType from EventSeat
 */
class EventSeatNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @param EventSeat            $object
     * @param array<string, mixed> $context
     *
     * @return EventSeatType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [];

        if (null !== $object->seat) {
            $data['seat'] = $this->normalizer->normalize($object->seat, $format, $context);
        }

        if (null !== $object->row) {
            $data['row'] = $this->normalizer->normalize($object->row, $format, $context);
        }

        if (null !== $object->section) {
            $data['section'] = $this->normalizer->normalize($object->section, $format, $context);
        }

        if (null !== $object->gate) {
            $data['gate'] = $this->normalizer->normalize($object->gate, $format, $context);
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof EventSeat;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [EventSeat::class => true];
    }
}
