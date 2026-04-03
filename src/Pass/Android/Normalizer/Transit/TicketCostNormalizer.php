<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Normalizer\Transit;

use Jolicode\WalletKit\Pass\Android\Model\Transit\TicketCost;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type TicketCostType from TicketCost
 */
class TicketCostNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @param TicketCost           $object
     * @param array<string, mixed> $context
     *
     * @return TicketCostType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [];

        if (null !== $object->faceValue) {
            $data['faceValue'] = $this->normalizer->normalize($object->faceValue, $format, $context);
        }

        if (null !== $object->purchasePrice) {
            $data['purchasePrice'] = $this->normalizer->normalize($object->purchasePrice, $format, $context);
        }

        if (null !== $object->discountMessage) {
            $data['discountMessage'] = $this->normalizer->normalize($object->discountMessage, $format, $context);
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof TicketCost;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [TicketCost::class => true];
    }
}
