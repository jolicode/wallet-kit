<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Normalizer\Shared;

use Jolicode\WalletKit\Pass\Android\Model\Shared\Review;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type ReviewType from Review
 */
class ReviewNormalizer implements NormalizerInterface
{
    /**
     * @param Review               $object
     * @param array<string, mixed> $context
     *
     * @return ReviewType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [];

        if (null !== $object->comments) {
            $data['comments'] = $object->comments;
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Review;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [Review::class => true];
    }
}
