<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Samsung\Normalizer\Generic;

use Jolicode\WalletKit\Pass\Samsung\Model\Generic\GenericAttributes;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type GenericAttributesType from GenericAttributes
 */
class GenericAttributesNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @param GenericAttributes    $object
     * @param array<string, mixed> $context
     *
     * @return GenericAttributesType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [
            'title' => $object->title,
            'providerName' => $object->providerName,
            'startDate' => $object->startDate,
            'noticeDesc' => $object->noticeDesc,
            'appLinkLogo' => $object->appLinkLogo,
            'appLinkName' => $object->appLinkName,
            'appLinkData' => $object->appLinkData,
        ];

        if (null !== $object->mainImg) {
            $data['mainImg'] = $object->mainImg;
        }

        if (null !== $object->subtitle) {
            $data['subtitle'] = $object->subtitle;
        }

        if (null !== $object->eventId) {
            $data['eventId'] = $object->eventId;
        }

        if (null !== $object->groupingId) {
            $data['groupingId'] = $object->groupingId;
        }

        if (null !== $object->endDate) {
            $data['endDate'] = $object->endDate;
        }

        if (null !== $object->logoImage) {
            $data['logoImage'] = $this->normalizer->normalize($object->logoImage, $format, $context);
        }

        if (null !== $object->coverImage) {
            $data['coverImage'] = $object->coverImage;
        }

        if (null !== $object->bgImage) {
            $data['bgImage'] = $object->bgImage;
        }

        if (null !== $object->bgColor) {
            $data['bgColor'] = $object->bgColor->hex();
        }

        if (null !== $object->fontColor) {
            $data['fontColor'] = $object->fontColor->hex();
        }

        if (null !== $object->blinkColor) {
            $data['blinkColor'] = $object->blinkColor->hex();
        }

        if (null !== $object->serial1) {
            $data['serial1'] = $this->normalizer->normalize($object->serial1, $format, $context);
        }

        if (null !== $object->serial2) {
            $data['serial2'] = $this->normalizer->normalize($object->serial2, $format, $context);
        }

        if (null !== $object->csInfo) {
            $data['csInfo'] = $object->csInfo;
        }

        if (null !== $object->providerViewLink) {
            $data['providerViewLink'] = $object->providerViewLink;
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

        if (null !== $object->noNetworkSupport) {
            $data['noNetworkSupportYn'] = $object->noNetworkSupport ? 'Y' : 'N';
        }

        if (null !== $object->privacyMode) {
            $data['privacyModeYn'] = $object->privacyMode ? 'Y' : 'N';
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof GenericAttributes;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [GenericAttributes::class => true];
    }
}
