<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Normalizer\Loyalty;

use Jolicode\WalletKit\Pass\Android\Model\Loyalty\LoyaltyClass;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type LoyaltyClassType from LoyaltyClass
 */
class LoyaltyClassNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @param LoyaltyClass         $object
     * @param array<string, mixed> $context
     *
     * @return LoyaltyClassType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [
            'id' => $object->id,
            'issuerName' => $object->issuerName,
            'reviewStatus' => $object->reviewStatus->value,
        ];

        if (null !== $object->programName) {
            $data['programName'] = $object->programName;
        }

        if (null !== $object->localizedProgramName) {
            $data['localizedProgramName'] = $this->normalizer->normalize($object->localizedProgramName, $format, $context);
        }

        if (null !== $object->programLogo) {
            $data['programLogo'] = $this->normalizer->normalize($object->programLogo, $format, $context);
        }

        if (null !== $object->wideProgramLogo) {
            $data['wideProgramLogo'] = $this->normalizer->normalize($object->wideProgramLogo, $format, $context);
        }

        if (null !== $object->hexBackgroundColor) {
            $data['hexBackgroundColor'] = $object->hexBackgroundColor->hex();
        }

        if (null !== $object->localizedIssuerName) {
            $data['localizedIssuerName'] = $this->normalizer->normalize($object->localizedIssuerName, $format, $context);
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
            $messages = [];
            foreach ($object->messages as $message) {
                $messages[] = $this->normalizer->normalize($message, $format, $context);
            }
            $data['messages'] = $messages;
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

        if (null !== $object->homepageUri) {
            $data['homepageUri'] = $this->normalizer->normalize($object->homepageUri, $format, $context);
        }

        if (null !== $object->appLinkData) {
            $data['appLinkData'] = $this->normalizer->normalize($object->appLinkData, $format, $context);
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
        return $data instanceof LoyaltyClass;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [LoyaltyClass::class => true];
    }
}
