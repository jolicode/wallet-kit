<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Normalizer\Shared;

use Jolicode\WalletKit\Pass\Android\Model\Shared\Message;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type GoogleMessageType from Message
 */
class MessageNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @param Message              $object
     * @param array<string, mixed> $context
     *
     * @return GoogleMessageType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [];

        if (null !== $object->header) {
            $data['header'] = $object->header;
        }

        if (null !== $object->body) {
            $data['body'] = $object->body;
        }

        if (null !== $object->id) {
            $data['id'] = $object->id;
        }

        if (null !== $object->messageType) {
            $data['messageType'] = $object->messageType->value;
        }

        if (null !== $object->localizedHeader) {
            $data['localizedHeader'] = $this->normalizer->normalize($object->localizedHeader, $format, $context);
        }

        if (null !== $object->localizedBody) {
            $data['localizedBody'] = $this->normalizer->normalize($object->localizedBody, $format, $context);
        }

        if (null !== $object->displayInterval) {
            $data['displayInterval'] = $this->normalizer->normalize($object->displayInterval, $format, $context);
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Message;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [Message::class => true];
    }
}
