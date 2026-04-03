<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Apple\Normalizer\SemanticTagType;

use Jolicode\WalletKit\Pass\Apple\Model\SemanticTagType\EventDateInfo;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type EventDateInfoType from EventDateInfo
 */
class EventDateInfoNormalizer implements NormalizerInterface
{
    /**
     * @param EventDateInfo        $object
     * @param array<string, mixed> $context
     *
     * @return EventDateInfoType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [];

        if (null !== $object->startDate) {
            $data['startDate'] = $object->startDate;
        }

        if (null !== $object->endDate) {
            $data['endDate'] = $object->endDate;
        }

        if (null !== $object->timeZone) {
            $data['timeZone'] = $object->timeZone;
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof EventDateInfo;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [EventDateInfo::class => true];
    }
}
