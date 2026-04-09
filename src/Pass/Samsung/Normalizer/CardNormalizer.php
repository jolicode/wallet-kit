<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Samsung\Normalizer;

use Jolicode\WalletKit\Pass\Samsung\Model\Card;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type CardEnvelopeType from Card
 */
class CardNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @param Card                 $object
     * @param array<string, mixed> $context
     *
     * @return CardEnvelopeType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [];
        foreach ($object->data as $cardData) {
            $data[] = $this->normalizer->normalize($cardData, $format, $context);
        }

        return [
            'card' => [
                'type' => $object->type->value,
                'subType' => $object->subType->value,
                'data' => $data,
            ],
        ];
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Card;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [Card::class => true];
    }
}
