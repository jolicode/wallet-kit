<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Normalizer\Offer;

use Jolicode\WalletKit\Pass\Android\Model\Offer\OfferObject;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type OfferObjectType from OfferObject
 */
class OfferObjectNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @param OfferObject          $object
     * @param array<string, mixed> $context
     *
     * @return OfferObjectType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [
            'id' => $object->id,
            'classId' => $object->classId,
            'state' => $object->state->value,
        ];

        if (null !== $object->barcode) {
            $data['barcode'] = $this->normalizer->normalize($object->barcode, $format, $context);
        }

        if (null !== $object->hexBackgroundColor) {
            $data['hexBackgroundColor'] = $object->hexBackgroundColor;
        }

        if (null !== $object->messages) {
            $messages = [];
            foreach ($object->messages as $message) {
                $messages[] = $this->normalizer->normalize($message, $format, $context);
            }
            $data['messages'] = $messages;
        }

        if (null !== $object->validTimeInterval) {
            $data['validTimeInterval'] = $this->normalizer->normalize($object->validTimeInterval, $format, $context);
        }

        if (null !== $object->smartTapRedemptionValue) {
            $data['smartTapRedemptionValue'] = $object->smartTapRedemptionValue;
        }

        if (null !== $object->disableExpirationNotification) {
            $data['disableExpirationNotification'] = $object->disableExpirationNotification;
        }

        if (null !== $object->imageModulesData) {
            $imageModulesData = [];
            foreach ($object->imageModulesData as $imageModuleData) {
                $imageModulesData[] = $this->normalizer->normalize($imageModuleData, $format, $context);
            }
            $data['imageModulesData'] = $imageModulesData;
        }

        if (null !== $object->textModulesData) {
            $textModulesData = [];
            foreach ($object->textModulesData as $textModuleData) {
                $textModulesData[] = $this->normalizer->normalize($textModuleData, $format, $context);
            }
            $data['textModulesData'] = $textModulesData;
        }

        if (null !== $object->linksModuleData) {
            $data['linksModuleData'] = $this->normalizer->normalize($object->linksModuleData, $format, $context);
        }

        if (null !== $object->appLinkData) {
            $data['appLinkData'] = $this->normalizer->normalize($object->appLinkData, $format, $context);
        }

        if (null !== $object->rotatingBarcode) {
            $data['rotatingBarcode'] = $this->normalizer->normalize($object->rotatingBarcode, $format, $context);
        }

        if (null !== $object->heroImage) {
            $data['heroImage'] = $this->normalizer->normalize($object->heroImage, $format, $context);
        }

        if (null !== $object->groupingInfo) {
            $data['groupingInfo'] = $this->normalizer->normalize($object->groupingInfo, $format, $context);
        }

        if (null !== $object->passConstraints) {
            $data['passConstraints'] = $this->normalizer->normalize($object->passConstraints, $format, $context);
        }

        if (null !== $object->linkedObjectIds) {
            $data['linkedObjectIds'] = $object->linkedObjectIds;
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof OfferObject;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [OfferObject::class => true];
    }
}
