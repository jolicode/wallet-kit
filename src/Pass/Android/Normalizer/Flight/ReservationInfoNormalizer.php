<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Normalizer\Flight;

use Jolicode\WalletKit\Pass\Android\Model\Flight\ReservationInfo;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type ReservationInfoType from ReservationInfo
 */
class ReservationInfoNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @param ReservationInfo      $object
     * @param array<string, mixed> $context
     *
     * @return ReservationInfoType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [];

        if (null !== $object->confirmationCode) {
            $data['confirmationCode'] = $object->confirmationCode;
        }

        if (null !== $object->eticketNumber) {
            $data['eticketNumber'] = $object->eticketNumber;
        }

        if (null !== $object->frequentFlyerInfo) {
            $data['frequentFlyerInfo'] = $this->normalizer->normalize($object->frequentFlyerInfo, $format, $context);
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof ReservationInfo;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [ReservationInfo::class => true];
    }
}
