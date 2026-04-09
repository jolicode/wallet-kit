<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Normalizer\Transit;

use Jolicode\WalletKit\Pass\Android\Model\Transit\TransitObject;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type TransitObjectType from TransitObject
 */
class TransitObjectNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @param TransitObject        $object
     * @param array<string, mixed> $context
     *
     * @return TransitObjectType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [
            'id' => $object->id,
            'classId' => $object->classId,
            'state' => $object->state->value,
            'tripType' => $object->tripType->value,
        ];

        if (null !== $object->ticketNumber) {
            $data['ticketNumber'] = $object->ticketNumber;
        }

        if (null !== $object->passengerType) {
            $data['passengerType'] = $object->passengerType->value;
        }

        if (null !== $object->passengerNames) {
            $data['passengerNames'] = $object->passengerNames;
        }

        if (null !== $object->tripId) {
            $data['tripId'] = $object->tripId;
        }

        if (null !== $object->ticketStatus) {
            $data['ticketStatus'] = $object->ticketStatus->value;
        }

        if (null !== $object->customTicketStatus) {
            $data['customTicketStatus'] = $this->normalizer->normalize($object->customTicketStatus, $format, $context);
        }

        if (null !== $object->concessionCategory) {
            $data['concessionCategory'] = $object->concessionCategory->value;
        }

        if (null !== $object->customConcessionCategory) {
            $data['customConcessionCategory'] = $this->normalizer->normalize($object->customConcessionCategory, $format, $context);
        }

        if (null !== $object->ticketRestrictions) {
            $data['ticketRestrictions'] = $this->normalizer->normalize($object->ticketRestrictions, $format, $context);
        }

        if (null !== $object->purchaseDetails) {
            $data['purchaseDetails'] = $this->normalizer->normalize($object->purchaseDetails, $format, $context);
        }

        if (null !== $object->ticketLeg) {
            $data['ticketLeg'] = $this->normalizer->normalize($object->ticketLeg, $format, $context);
        }

        if (null !== $object->ticketLegs) {
            $normalized = [];
            foreach ($object->ticketLegs as $ticketLeg) {
                $normalized[] = $this->normalizer->normalize($ticketLeg, $format, $context);
            }
            $data['ticketLegs'] = $normalized;
        }

        if (null !== $object->hexBackgroundColor) {
            $data['hexBackgroundColor'] = $object->hexBackgroundColor->hex();
        }

        if (null !== $object->barcode) {
            $data['barcode'] = $this->normalizer->normalize($object->barcode, $format, $context);
        }

        if (null !== $object->messages) {
            $normalized = [];
            foreach ($object->messages as $message) {
                $normalized[] = $this->normalizer->normalize($message, $format, $context);
            }
            $data['messages'] = $normalized;
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
            $normalized = [];
            foreach ($object->imageModulesData as $imageModuleData) {
                $normalized[] = $this->normalizer->normalize($imageModuleData, $format, $context);
            }
            $data['imageModulesData'] = $normalized;
        }

        if (null !== $object->textModulesData) {
            $normalized = [];
            foreach ($object->textModulesData as $textModuleData) {
                $normalized[] = $this->normalizer->normalize($textModuleData, $format, $context);
            }
            $data['textModulesData'] = $normalized;
        }

        if (null !== $object->linksModuleData) {
            $data['linksModuleData'] = $this->normalizer->normalize($object->linksModuleData, $format, $context);
        }

        if (null !== $object->appLinkData) {
            $data['appLinkData'] = $this->normalizer->normalize($object->appLinkData, $format, $context);
        }

        if (null !== $object->activationStatus) {
            $data['activationStatus'] = $this->normalizer->normalize($object->activationStatus, $format, $context);
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
        return $data instanceof TransitObject;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [TransitObject::class => true];
    }
}
