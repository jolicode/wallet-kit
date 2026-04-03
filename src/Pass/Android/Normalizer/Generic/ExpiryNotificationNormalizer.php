<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Normalizer\Generic;

use Jolicode\WalletKit\Pass\Android\Model\Generic\ExpiryNotification;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type ExpiryNotificationType from ExpiryNotification
 */
class ExpiryNotificationNormalizer implements NormalizerInterface
{
    /**
     * @param ExpiryNotification   $object
     * @param array<string, mixed> $context
     *
     * @return ExpiryNotificationType
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
        return $data instanceof ExpiryNotification;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [ExpiryNotification::class => true];
    }
}
