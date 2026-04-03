<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Apple\Normalizer;

use Jolicode\WalletKit\Pass\Apple\Model\Pass;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type PassType from Pass
 */
class PassNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @param Pass                 $object
     * @param array<string, mixed> $context
     *
     * @return PassType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [
            'formatVersion' => $object->formatVersion,
            'passTypeIdentifier' => $object->passTypeIdentifier,
            'serialNumber' => $object->serialNumber,
            'teamIdentifier' => $object->teamIdentifier,
            'organizationName' => $object->organizationName,
            'description' => $object->description,
        ];

        $data[$object->passType->value] = $this->normalizer->normalize($object->structure, $format, $context);

        if ([] !== $object->barcodes) {
            $barcodes = [];
            foreach ($object->barcodes as $barcode) {
                $barcodes[] = $this->normalizer->normalize($barcode, $format, $context);
            }
            $data['barcodes'] = $barcodes;
        }

        if (null !== $object->nfc) {
            $data['nfc'] = $this->normalizer->normalize($object->nfc, $format, $context);
        }

        if (null !== $object->webServiceURL) {
            $data['webServiceURL'] = $object->webServiceURL;
        }

        if (null !== $object->authenticationToken) {
            $data['authenticationToken'] = $object->authenticationToken;
        }

        if (null !== $object->appLaunchURL) {
            $data['appLaunchURL'] = $object->appLaunchURL;
        }

        if ([] !== $object->associatedStoreIdentifiers) {
            $data['associatedStoreIdentifiers'] = $object->associatedStoreIdentifiers;
        }

        if (null !== $object->backgroundColor) {
            $data['backgroundColor'] = $object->backgroundColor;
        }

        if (null !== $object->foregroundColor) {
            $data['foregroundColor'] = $object->foregroundColor;
        }

        if (null !== $object->labelColor) {
            $data['labelColor'] = $object->labelColor;
        }

        if (null !== $object->logoText) {
            $data['logoText'] = $object->logoText;
        }

        if (null !== $object->suppressStripShine) {
            $data['suppressStripShine'] = $object->suppressStripShine;
        }

        if (null !== $object->locations) {
            $locations = [];
            foreach ($object->locations as $location) {
                $locations[] = $this->normalizer->normalize($location, $format, $context);
            }
            $data['locations'] = $locations;
        }

        if (null !== $object->beacons) {
            $beacons = [];
            foreach ($object->beacons as $beacon) {
                $beacons[] = $this->normalizer->normalize($beacon, $format, $context);
            }
            $data['beacons'] = $beacons;
        }

        if (null !== $object->relevantDate) {
            $data['relevantDate'] = $object->relevantDate;
        }

        if (null !== $object->relevantDates) {
            $relevantDates = [];
            foreach ($object->relevantDates as $relevantDate) {
                $relevantDates[] = $this->normalizer->normalize($relevantDate, $format, $context);
            }
            $data['relevantDates'] = $relevantDates;
        }

        if (null !== $object->maxDistance) {
            $data['maxDistance'] = $object->maxDistance;
        }

        if (null !== $object->expirationDate) {
            $data['expirationDate'] = $object->expirationDate;
        }

        if (null !== $object->voided) {
            $data['voided'] = $object->voided;
        }

        if (null !== $object->groupingIdentifier) {
            $data['groupingIdentifier'] = $object->groupingIdentifier;
        }

        if (null !== $object->sharingProhibited) {
            $data['sharingProhibited'] = $object->sharingProhibited;
        }

        if (null !== $object->semantics) {
            $data['semantics'] = $this->normalizer->normalize($object->semantics, $format, $context);
        }

        if (null !== $object->preferredStyleSchemes) {
            $data['preferredStyleSchemes'] = $object->preferredStyleSchemes;
        }

        if (null !== $object->userInfo) {
            $data['userInfo'] = $object->userInfo;
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Pass;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [Pass::class => true];
    }
}
