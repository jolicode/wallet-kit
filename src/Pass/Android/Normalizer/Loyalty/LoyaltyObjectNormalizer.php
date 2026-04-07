<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Normalizer\Loyalty;

use Jolicode\WalletKit\Pass\Android\Model\Loyalty\LoyaltyObject;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type LoyaltyObjectType from LoyaltyObject
 */
class LoyaltyObjectNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @param LoyaltyObject        $object
     * @param array<string, mixed> $context
     *
     * @return LoyaltyObjectType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [
            'id' => $object->id,
            'classId' => $object->classId,
            'state' => $object->state->value,
        ];

        if (null !== $object->accountName) {
            $data['accountName'] = $object->accountName;
        }

        if (null !== $object->accountId) {
            $data['accountId'] = $object->accountId;
        }

        if (null !== $object->loyaltyPoints) {
            $data['loyaltyPoints'] = $this->normalizer->normalize($object->loyaltyPoints, $format, $context);
        }

        if (null !== $object->secondaryLoyaltyPoints) {
            $data['secondaryLoyaltyPoints'] = $this->normalizer->normalize($object->secondaryLoyaltyPoints, $format, $context);
        }

        if (null !== $object->linkedOfferIds) {
            $data['linkedOfferIds'] = $object->linkedOfferIds;
        }

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

        if (null !== $object->merchantLocations) {
            $merchantLocations = [];
            foreach ($object->merchantLocations as $merchantLocation) {
                $merchantLocations[] = $this->normalizer->normalize($merchantLocation, $format, $context);
            }
            $data['merchantLocations'] = $merchantLocations;
        }

        if (null !== $object->valueAddedModuleData) {
            $valueAddedModuleData = [];
            foreach ($object->valueAddedModuleData as $valueAddedModuleDatum) {
                $valueAddedModuleData[] = $this->normalizer->normalize($valueAddedModuleDatum, $format, $context);
            }
            $data['valueAddedModuleData'] = $valueAddedModuleData;
        }

        if (null !== $object->saveRestrictions) {
            $data['saveRestrictions'] = $this->normalizer->normalize($object->saveRestrictions, $format, $context);
        }

        if (null !== $object->notifyPreference) {
            $data['notifyPreference'] = $object->notifyPreference->value;
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof LoyaltyObject;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [LoyaltyObject::class => true];
    }
}
