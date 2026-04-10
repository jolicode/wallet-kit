<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Samsung\Normalizer\PayAsYouGo;

use Jolicode\WalletKit\Pass\Samsung\Model\PayAsYouGo\PayAsYouGoAttributes;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type PayAsYouGoAttributesType from PayAsYouGoAttributes
 */
class PayAsYouGoAttributesNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @param PayAsYouGoAttributes $object
     * @param array<string, mixed> $context
     *
     * @return PayAsYouGoAttributesType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [
            'title' => $object->title,
            'noticeDesc' => $object->noticeDesc,
            'appLinkLogo' => $object->appLinkLogo,
            'appLinkName' => $object->appLinkName,
            'appLinkData' => $object->appLinkData,
            'barcode' => $this->normalizer->normalize($object->barcode, $format, $context),
        ];

        if (null !== $object->subtitle1) {
            $data['subtitle1'] = $object->subtitle1;
        }

        if (null !== $object->logoImage) {
            $data['logoImage'] = $this->normalizer->normalize($object->logoImage, $format, $context);
        }

        if (null !== $object->providerName) {
            $data['providerName'] = $object->providerName;
        }

        if (null !== $object->holderName) {
            $data['holderName'] = $object->holderName;
        }

        if (null !== $object->startDate) {
            $data['startDate'] = $object->startDate;
        }

        if (null !== $object->endDate) {
            $data['endDate'] = $object->endDate;
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

        if (null !== $object->blinkColor) {
            $data['blinkColor'] = $object->blinkColor->hex();
        }

        if (null !== $object->csInfo) {
            $data['csInfo'] = $object->csInfo;
        }

        if (null !== $object->identifier) {
            $data['identifier'] = $object->identifier;
        }

        if (null !== $object->grade) {
            $data['grade'] = $object->grade;
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
        return $data instanceof PayAsYouGoAttributes;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [PayAsYouGoAttributes::class => true];
    }
}
