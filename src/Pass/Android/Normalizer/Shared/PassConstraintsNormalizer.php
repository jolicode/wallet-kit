<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Normalizer\Shared;

use Jolicode\WalletKit\Pass\Android\Model\Shared\PassConstraints;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type PassConstraintsType from PassConstraints
 */
class PassConstraintsNormalizer implements NormalizerInterface
{
    /**
     * @param PassConstraints      $object
     * @param array<string, mixed> $context
     *
     * @return PassConstraintsType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [];

        if (null !== $object->screenshotEligibility) {
            $data['screenshotEligibility'] = $object->screenshotEligibility->value;
        }

        if (null !== $object->nfcConstraint) {
            $data['nfcConstraint'] = array_map(static fn ($item) => $item->value, $object->nfcConstraint);
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof PassConstraints;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [PassConstraints::class => true];
    }
}
