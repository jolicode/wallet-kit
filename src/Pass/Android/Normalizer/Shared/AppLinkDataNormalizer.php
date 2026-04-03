<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Normalizer\Shared;

use Jolicode\WalletKit\Pass\Android\Model\Shared\AppLinkData;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type AppLinkDataType from AppLinkData
 */
class AppLinkDataNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @param AppLinkData          $object
     * @param array<string, mixed> $context
     *
     * @return AppLinkDataType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [];

        if (null !== $object->androidAppLinkInfo) {
            $data['androidAppLinkInfo'] = $this->normalizer->normalize($object->androidAppLinkInfo, $format, $context);
        }

        if (null !== $object->webAppLinkInfo) {
            $data['webAppLinkInfo'] = $this->normalizer->normalize($object->webAppLinkInfo, $format, $context);
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof AppLinkData;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [AppLinkData::class => true];
    }
}
