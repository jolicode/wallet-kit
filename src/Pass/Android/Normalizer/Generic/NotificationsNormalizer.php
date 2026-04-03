<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Normalizer\Generic;

use Jolicode\WalletKit\Pass\Android\Model\Generic\Notifications;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type NotificationsType from Notifications
 */
class NotificationsNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @param Notifications        $object
     * @param array<string, mixed> $context
     *
     * @return NotificationsType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [];

        if (null !== $object->expiryNotification) {
            $data['expiryNotification'] = $this->normalizer->normalize($object->expiryNotification, $format, $context);
        }

        if (null !== $object->upcomingNotification) {
            $data['upcomingNotification'] = $this->normalizer->normalize($object->upcomingNotification, $format, $context);
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Notifications;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [Notifications::class => true];
    }
}
