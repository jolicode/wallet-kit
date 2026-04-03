<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Normalizer\Shared;

use Jolicode\WalletKit\Pass\Android\Model\Shared\GroupingInfo;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type GroupingInfoType from GroupingInfo
 */
class GroupingInfoNormalizer implements NormalizerInterface
{
    /**
     * @param GroupingInfo         $object
     * @param array<string, mixed> $context
     *
     * @return GroupingInfoType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [];

        if (null !== $object->groupingId) {
            $data['groupingId'] = $object->groupingId;
        }

        if (null !== $object->sortIndex) {
            $data['sortIndex'] = $object->sortIndex;
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof GroupingInfo;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [GroupingInfo::class => true];
    }
}
