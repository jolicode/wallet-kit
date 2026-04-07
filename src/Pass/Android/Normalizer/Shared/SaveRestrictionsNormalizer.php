<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Normalizer\Shared;

use Jolicode\WalletKit\Pass\Android\Model\Shared\SaveRestrictions;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type SaveRestrictionsType from SaveRestrictions
 */
class SaveRestrictionsNormalizer implements NormalizerInterface
{
    /**
     * @param SaveRestrictions     $object
     * @param array<string, mixed> $context
     *
     * @return SaveRestrictionsType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [];

        if (null !== $object->restrictionStatus) {
            $data['restrictionStatus'] = $object->restrictionStatus->value;
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof SaveRestrictions;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [SaveRestrictions::class => true];
    }
}
