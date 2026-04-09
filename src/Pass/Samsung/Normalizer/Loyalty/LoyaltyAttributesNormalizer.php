<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Samsung\Normalizer\Loyalty;

use Jolicode\WalletKit\Pass\Samsung\Model\Loyalty\LoyaltyAttributes;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type LoyaltyAttributesType from LoyaltyAttributes
 */
class LoyaltyAttributesNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @param LoyaltyAttributes    $object
     * @param array<string, mixed> $context
     *
     * @return LoyaltyAttributesType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [
            'title' => $object->title,
            'providerName' => $object->providerName,
            'appLinkLogo' => $object->appLinkLogo,
            'appLinkName' => $object->appLinkName,
            'appLinkData' => $object->appLinkData,
        ];

        if (null !== $object->logoImage) {
            $data['logoImage'] = $this->normalizer->normalize($object->logoImage, $format, $context);
        }

        if (null !== $object->startDate) {
            $data['startDate'] = $object->startDate;
        }

        if (null !== $object->endDate) {
            $data['endDate'] = $object->endDate;
        }

        if (null !== $object->barcode) {
            $data['barcode'] = $this->normalizer->normalize($object->barcode, $format, $context);
        }

        if (null !== $object->bgColor) {
            $data['bgColor'] = $object->bgColor;
        }

        if (null !== $object->fontColor) {
            $data['fontColor'] = $object->fontColor;
        }

        if (null !== $object->bgImage) {
            $data['bgImage'] = $object->bgImage;
        }

        if (null !== $object->blinkColor) {
            $data['blinkColor'] = $object->blinkColor;
        }

        if (null !== $object->noticeDesc) {
            $data['noticeDesc'] = $object->noticeDesc;
        }

        if (null !== $object->csInfo) {
            $data['csInfo'] = $object->csInfo;
        }

        if (null !== $object->merchantId) {
            $data['merchantId'] = $object->merchantId;
        }

        if (null !== $object->merchantName) {
            $data['merchantName'] = $object->merchantName;
        }

        if (null !== $object->amount) {
            $data['amount'] = $object->amount;
        }

        if (null !== $object->balance) {
            $data['balance'] = $object->balance;
        }

        if (null !== $object->summaryUrl) {
            $data['summaryUrl'] = $object->summaryUrl;
        }

        if (null !== $object->locations) {
            $locations = [];
            foreach ($object->locations as $location) {
                $locations[] = $this->normalizer->normalize($location, $format, $context);
            }
            $data['locations'] = $locations;
        }

        if (null !== $object->preventCapture) {
            $data['preventCaptureYn'] = $object->preventCapture ? 'Y' : 'N';
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof LoyaltyAttributes;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [LoyaltyAttributes::class => true];
    }
}
