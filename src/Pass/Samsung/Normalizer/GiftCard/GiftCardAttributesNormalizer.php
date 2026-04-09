<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Samsung\Normalizer\GiftCard;

use Jolicode\WalletKit\Pass\Samsung\Model\GiftCard\GiftCardAttributes;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type GiftCardAttributesType from GiftCardAttributes
 */
class GiftCardAttributesNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @param GiftCardAttributes   $object
     * @param array<string, mixed> $context
     *
     * @return GiftCardAttributesType
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

        if (null !== $object->user) {
            $data['user'] = $object->user;
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
            $data['bgColor'] = $object->bgColor->hex();
        }

        if (null !== $object->fontColor) {
            $data['fontColor'] = $object->fontColor->hex();
        }

        if (null !== $object->bgImage) {
            $data['bgImage'] = $object->bgImage;
        }

        if (null !== $object->mainImg) {
            $data['mainImg'] = $object->mainImg;
        }

        if (null !== $object->blinkColor) {
            $data['blinkColor'] = $object->blinkColor->hex();
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
        return $data instanceof GiftCardAttributes;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [GiftCardAttributes::class => true];
    }
}
