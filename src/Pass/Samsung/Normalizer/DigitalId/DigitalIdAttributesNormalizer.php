<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Samsung\Normalizer\DigitalId;

use Jolicode\WalletKit\Pass\Samsung\Model\DigitalId\DigitalIdAttributes;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type DigitalIdAttributesType from DigitalIdAttributes
 */
class DigitalIdAttributesNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @param DigitalIdAttributes  $object
     * @param array<string, mixed> $context
     *
     * @return DigitalIdAttributesType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [
            'title' => $object->title,
            'holderName' => $object->holderName,
            'identifier' => $object->identifier,
            'issueDate' => $object->issueDate,
            'providerName' => $object->providerName,
            'csInfo' => $object->csInfo,
        ];

        if (null !== $object->logoImage) {
            $data['logoImage'] = $this->normalizer->normalize($object->logoImage, $format, $context);
        }

        if (null !== $object->secondHolderName) {
            $data['secondHolderName'] = $object->secondHolderName;
        }

        if (null !== $object->organization) {
            $data['organization'] = $object->organization;
        }

        if (null !== $object->position) {
            $data['position'] = $object->position;
        }

        if (null !== $object->idNumber) {
            $data['idNumber'] = $object->idNumber;
        }

        if (null !== $object->address) {
            $data['address'] = $object->address;
        }

        if (null !== $object->birthdate) {
            $data['birthdate'] = $object->birthdate;
        }

        if (null !== $object->gender) {
            $data['gender'] = $object->gender;
        }

        if (null !== $object->classification) {
            $data['classification'] = $object->classification;
        }

        if (null !== $object->expiry) {
            $data['expiry'] = $object->expiry;
        }

        if (null !== $object->issuerName) {
            $data['issuerName'] = $object->issuerName;
        }

        if (null !== $object->extraInfo) {
            $data['extraInfo'] = $object->extraInfo;
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

        if (null !== $object->bgImage) {
            $data['bgImage'] = $object->bgImage;
        }

        if (null !== $object->coverImage) {
            $data['coverImage'] = $object->coverImage;
        }

        if (null !== $object->blinkColor) {
            $data['blinkColor'] = $object->blinkColor->hex();
        }

        if (null !== $object->appLinkLogo) {
            $data['appLinkLogo'] = $object->appLinkLogo;
        }

        if (null !== $object->appLinkName) {
            $data['appLinkName'] = $object->appLinkName;
        }

        if (null !== $object->appLinkData) {
            $data['appLinkData'] = $object->appLinkData;
        }

        if (null !== $object->preventCapture) {
            $data['preventCaptureYn'] = $object->preventCapture ? 'Y' : 'N';
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
        return $data instanceof DigitalIdAttributes;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [DigitalIdAttributes::class => true];
    }
}
