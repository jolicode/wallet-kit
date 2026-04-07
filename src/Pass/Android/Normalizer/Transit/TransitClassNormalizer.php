<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Normalizer\Transit;

use Jolicode\WalletKit\Pass\Android\Model\Transit\TransitClass;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type TransitClassType from TransitClass
 */
class TransitClassNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @param TransitClass         $object
     * @param array<string, mixed> $context
     *
     * @return TransitClassType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [
            'id' => $object->id,
            'issuerName' => $object->issuerName,
            'reviewStatus' => $object->reviewStatus->value,
            'transitType' => $object->transitType->value,
        ];

        if (null !== $object->localizedIssuerName) {
            $data['localizedIssuerName'] = $this->normalizer->normalize($object->localizedIssuerName, $format, $context);
        }

        if (null !== $object->transitOperatorName) {
            $data['transitOperatorName'] = $this->normalizer->normalize($object->transitOperatorName, $format, $context);
        }

        if (null !== $object->localizedTransitOperatorName) {
            $data['localizedTransitOperatorName'] = $this->normalizer->normalize($object->localizedTransitOperatorName, $format, $context);
        }

        if (null !== $object->logo) {
            $data['logo'] = $this->normalizer->normalize($object->logo, $format, $context);
        }

        if (null !== $object->wideLogo) {
            $data['wideLogo'] = $this->normalizer->normalize($object->wideLogo, $format, $context);
        }

        if (null !== $object->hexBackgroundColor) {
            $data['hexBackgroundColor'] = $object->hexBackgroundColor;
        }

        if (null !== $object->countryCode) {
            $data['countryCode'] = $object->countryCode;
        }

        if (null !== $object->heroImage) {
            $data['heroImage'] = $this->normalizer->normalize($object->heroImage, $format, $context);
        }

        if (null !== $object->enableSmartTap) {
            $data['enableSmartTap'] = $object->enableSmartTap;
        }

        if (null !== $object->redemptionIssuers) {
            $data['redemptionIssuers'] = $object->redemptionIssuers;
        }

        if (null !== $object->multipleDevicesAndHoldersAllowedStatus) {
            $data['multipleDevicesAndHoldersAllowedStatus'] = $object->multipleDevicesAndHoldersAllowedStatus->value;
        }

        if (null !== $object->callbackOptions) {
            $data['callbackOptions'] = $this->normalizer->normalize($object->callbackOptions, $format, $context);
        }

        if (null !== $object->securityAnimation) {
            $data['securityAnimation'] = $this->normalizer->normalize($object->securityAnimation, $format, $context);
        }

        if (null !== $object->viewUnlockRequirement) {
            $data['viewUnlockRequirement'] = $object->viewUnlockRequirement->value;
        }

        if (null !== $object->messages) {
            $normalized = [];
            foreach ($object->messages as $message) {
                $normalized[] = $this->normalizer->normalize($message, $format, $context);
            }
            $data['messages'] = $normalized;
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

        if (null !== $object->homepageUri) {
            $data['homepageUri'] = $this->normalizer->normalize($object->homepageUri, $format, $context);
        }

        if (null !== $object->appLinkData) {
            $data['appLinkData'] = $this->normalizer->normalize($object->appLinkData, $format, $context);
        }

        if (null !== $object->enableSingleLegItinerary) {
            $data['enableSingleLegItinerary'] = $object->enableSingleLegItinerary;
        }

        if (null !== $object->languageOverride) {
            $data['languageOverride'] = $object->languageOverride;
        }

        if (null !== $object->customTransitTypeLabel) {
            $data['customTransitTypeLabel'] = $this->normalizer->normalize($object->customTransitTypeLabel, $format, $context);
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

        if (null !== $object->notifyPreference) {
            $data['notifyPreference'] = $object->notifyPreference->value;
        }

        if (null !== $object->review) {
            $data['review'] = $this->normalizer->normalize($object->review, $format, $context);
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof TransitClass;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [TransitClass::class => true];
    }
}
