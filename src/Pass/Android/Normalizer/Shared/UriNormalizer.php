<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Normalizer\Shared;

use Jolicode\WalletKit\Pass\Android\Model\Shared\Uri;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type UriType from Uri
 */
class UriNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @param Uri                  $object
     * @param array<string, mixed> $context
     *
     * @return UriType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [];

        if (null !== $object->uri) {
            $data['uri'] = $object->uri;
        }

        if (null !== $object->description) {
            $data['description'] = $object->description;
        }

        if (null !== $object->localizedDescription) {
            $data['localizedDescription'] = $this->normalizer->normalize($object->localizedDescription, $format, $context);
        }

        if (null !== $object->id) {
            $data['id'] = $object->id;
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Uri;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [Uri::class => true];
    }
}
