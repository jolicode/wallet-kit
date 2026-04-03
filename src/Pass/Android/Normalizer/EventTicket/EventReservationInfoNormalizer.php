<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Normalizer\EventTicket;

use Jolicode\WalletKit\Pass\Android\Model\EventTicket\EventReservationInfo;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type EventReservationInfoType from EventReservationInfo
 */
class EventReservationInfoNormalizer implements NormalizerInterface
{
    /**
     * @param EventReservationInfo $object
     * @param array<string, mixed> $context
     *
     * @return EventReservationInfoType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [];

        if (null !== $object->confirmationCode) {
            $data['confirmationCode'] = $object->confirmationCode;
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof EventReservationInfo;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [EventReservationInfo::class => true];
    }
}
