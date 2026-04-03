<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Normalizer\Transit;

use Jolicode\WalletKit\Pass\Android\Model\Transit\PurchaseDetails;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type PurchaseDetailsType from PurchaseDetails
 */
class PurchaseDetailsNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @param PurchaseDetails      $object
     * @param array<string, mixed> $context
     *
     * @return PurchaseDetailsType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [];

        if (null !== $object->purchaseReceiptNumber) {
            $data['purchaseReceiptNumber'] = $object->purchaseReceiptNumber;
        }

        if (null !== $object->purchaseDateTime) {
            $data['purchaseDateTime'] = $object->purchaseDateTime;
        }

        if (null !== $object->accountId) {
            $data['accountId'] = $object->accountId;
        }

        if (null !== $object->confirmationCode) {
            $data['confirmationCode'] = $object->confirmationCode;
        }

        if (null !== $object->ticketCost) {
            $data['ticketCost'] = $this->normalizer->normalize($object->ticketCost, $format, $context);
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof PurchaseDetails;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [PurchaseDetails::class => true];
    }
}
