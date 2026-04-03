<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Normalizer\Shared;

use Jolicode\WalletKit\Pass\Android\Model\Shared\GoogleDateTime;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type GoogleDateTimeType from GoogleDateTime
 */
class GoogleDateTimeNormalizer implements NormalizerInterface
{
    /**
     * @param GoogleDateTime       $object
     * @param array<string, mixed> $context
     *
     * @return GoogleDateTimeType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [];

        if (null !== $object->date) {
            $data['date'] = $object->date;
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof GoogleDateTime;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [GoogleDateTime::class => true];
    }
}
