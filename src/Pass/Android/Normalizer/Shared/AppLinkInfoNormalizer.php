<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Normalizer\Shared;

use Jolicode\WalletKit\Pass\Android\Model\Shared\AppLinkInfo;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type AppLinkInfoType from AppLinkInfo
 */
class AppLinkInfoNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @param AppLinkInfo          $object
     * @param array<string, mixed> $context
     *
     * @return AppLinkInfoType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [];

        if (null !== $object->appLogoImage) {
            $data['appLogoImage'] = $this->normalizer->normalize($object->appLogoImage, $format, $context);
        }

        if (null !== $object->title) {
            $data['title'] = $this->normalizer->normalize($object->title, $format, $context);
        }

        if (null !== $object->description) {
            $data['description'] = $this->normalizer->normalize($object->description, $format, $context);
        }

        if (null !== $object->appTarget) {
            $data['appTarget'] = $this->normalizer->normalize($object->appTarget, $format, $context);
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof AppLinkInfo;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [AppLinkInfo::class => true];
    }
}
