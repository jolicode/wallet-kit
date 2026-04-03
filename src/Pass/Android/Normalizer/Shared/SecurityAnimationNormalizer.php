<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Normalizer\Shared;

use Jolicode\WalletKit\Pass\Android\Model\Shared\SecurityAnimation;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type SecurityAnimationType from SecurityAnimation
 */
class SecurityAnimationNormalizer implements NormalizerInterface
{
    /**
     * @param SecurityAnimation    $object
     * @param array<string, mixed> $context
     *
     * @return SecurityAnimationType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [];

        if (null !== $object->animationType) {
            $data['animationType'] = $object->animationType->value;
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof SecurityAnimation;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [SecurityAnimation::class => true];
    }
}
