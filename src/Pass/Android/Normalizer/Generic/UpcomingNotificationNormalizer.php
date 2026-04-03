<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Normalizer\Generic;

use Jolicode\WalletKit\Pass\Android\Model\Generic\UpcomingNotification;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type UpcomingNotificationType from UpcomingNotification
 */
class UpcomingNotificationNormalizer implements NormalizerInterface
{
    /**
     * @param UpcomingNotification $object
     * @param array<string, mixed> $context
     *
     * @return UpcomingNotificationType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [];

        if (null !== $object->enableNotification) {
            $data['enableNotification'] = $object->enableNotification;
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof UpcomingNotification;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [UpcomingNotification::class => true];
    }
}
