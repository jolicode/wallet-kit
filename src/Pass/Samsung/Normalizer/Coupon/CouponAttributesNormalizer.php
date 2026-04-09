<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Samsung\Normalizer\Coupon;

use Jolicode\WalletKit\Pass\Samsung\Model\Coupon\CouponAttributes;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type CouponAttributesType from CouponAttributes
 */
class CouponAttributesNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @param CouponAttributes     $object
     * @param array<string, mixed> $context
     *
     * @return CouponAttributesType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [
            'title' => $object->title,
            'appLinkLogo' => $object->appLinkLogo,
            'appLinkName' => $object->appLinkName,
            'appLinkData' => $object->appLinkData,
            'issueDate' => $object->issueDate,
            'expiry' => $object->expiry,
        ];

        if (null !== $object->mainImg) {
            $data['mainImg'] = $object->mainImg;
        }

        if (null !== $object->logoImage) {
            $data['logoImage'] = $this->normalizer->normalize($object->logoImage, $format, $context);
        }

        if (null !== $object->brandName) {
            $data['brandName'] = $object->brandName;
        }

        if (null !== $object->noticeDesc) {
            $data['noticeDesc'] = $object->noticeDesc;
        }

        if (null !== $object->barcode) {
            $data['barcode'] = $this->normalizer->normalize($object->barcode, $format, $context);
        }

        if (null !== $object->bgColor) {
            $data['bgColor'] = $object->bgColor->hex();
        }

        if (null !== $object->fontColor) {
            $data['fontColor'] = $object->fontColor->hex();
        }

        if (null !== $object->balance) {
            $data['balance'] = $object->balance;
        }

        if (null !== $object->summaryUrl) {
            $data['summaryUrl'] = $object->summaryUrl;
        }

        if (null !== $object->editable) {
            $data['editableYn'] = $object->editable ? 'Y' : 'N';
        }

        if (null !== $object->deletable) {
            $data['deletableYn'] = $object->deletable ? 'Y' : 'N';
        }

        if (null !== $object->displayRedeemButton) {
            $data['displayRedeemButtonYn'] = $object->displayRedeemButton ? 'Y' : 'N';
        }

        if (null !== $object->notification) {
            $data['notificationYn'] = $object->notification ? 'Y' : 'N';
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
        return $data instanceof CouponAttributes;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [CouponAttributes::class => true];
    }
}
