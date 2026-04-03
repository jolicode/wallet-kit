<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Normalizer\Transit;

use Jolicode\WalletKit\Pass\Android\Model\Transit\TicketRestrictions;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type TicketRestrictionsType from TicketRestrictions
 */
class TicketRestrictionsNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @param TicketRestrictions   $object
     * @param array<string, mixed> $context
     *
     * @return TicketRestrictionsType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [];

        if (null !== $object->routeRestrictions) {
            $data['routeRestrictions'] = $this->normalizer->normalize($object->routeRestrictions, $format, $context);
        }

        if (null !== $object->routeRestrictionsDetails) {
            $data['routeRestrictionsDetails'] = $this->normalizer->normalize($object->routeRestrictionsDetails, $format, $context);
        }

        if (null !== $object->timeRestrictions) {
            $data['timeRestrictions'] = $this->normalizer->normalize($object->timeRestrictions, $format, $context);
        }

        if (null !== $object->otherRestrictions) {
            $data['otherRestrictions'] = $this->normalizer->normalize($object->otherRestrictions, $format, $context);
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof TicketRestrictions;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [TicketRestrictions::class => true];
    }
}
