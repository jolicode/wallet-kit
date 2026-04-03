<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Normalizer\Shared;

use Jolicode\WalletKit\Pass\Android\Model\Shared\CallbackOptions;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type CallbackOptionsType from CallbackOptions
 */
class CallbackOptionsNormalizer implements NormalizerInterface
{
    /**
     * @param CallbackOptions      $object
     * @param array<string, mixed> $context
     *
     * @return CallbackOptionsType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [];

        if (null !== $object->url) {
            $data['url'] = $object->url;
        }

        if (null !== $object->updateRequestUrl) {
            $data['updateRequestUrl'] = $object->updateRequestUrl;
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof CallbackOptions;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [CallbackOptions::class => true];
    }
}
