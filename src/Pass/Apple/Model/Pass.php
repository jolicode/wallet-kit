<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Apple\Model;

use Jolicode\WalletKit\Common\Color;

/**
 * @phpstan-import-type PassStructureType from PassStructure
 * @phpstan-import-type BarcodeType from Barcode
 * @phpstan-import-type NfcType from Nfc
 * @phpstan-import-type LocationType from Location
 * @phpstan-import-type BeaconType from Beacon
 * @phpstan-import-type RelevantDateType from RelevantDate
 * @phpstan-import-type SemanticTagsType from SemanticTags
 *
 * @phpstan-type PassType array{
 *     formatVersion: int,
 *     passTypeIdentifier: string,
 *     serialNumber: string,
 *     teamIdentifier: string,
 *     organizationName: string,
 *     description: string,
 *     boardingPass?: PassStructureType,
 *     coupon?: PassStructureType,
 *     eventTicket?: PassStructureType,
 *     generic?: PassStructureType,
 *     storeCard?: PassStructureType,
 *     barcodes?: list<BarcodeType>,
 *     nfc?: NfcType,
 *     webServiceURL?: string,
 *     authenticationToken?: string,
 *     appLaunchURL?: string,
 *     associatedStoreIdentifiers?: list<int>,
 *     backgroundColor?: string,
 *     foregroundColor?: string,
 *     labelColor?: string,
 *     logoText?: string,
 *     suppressStripShine?: bool,
 *     locations?: list<LocationType>,
 *     beacons?: list<BeaconType>,
 *     relevantDate?: string,
 *     relevantDates?: list<RelevantDateType>,
 *     maxDistance?: float,
 *     expirationDate?: string,
 *     voided?: bool,
 *     groupingIdentifier?: string,
 *     sharingProhibited?: bool,
 *     semantics?: SemanticTagsType,
 *     preferredStyleSchemes?: list<string>,
 *     userInfo?: array<string, mixed>,
 * }
 */
class Pass
{
    /**
     * @param Barcode[]                 $barcodes
     * @param list<int>                 $associatedStoreIdentifiers
     * @param Location[]|null           $locations
     * @param Beacon[]|null             $beacons
     * @param RelevantDate[]|null       $relevantDates
     * @param string[]|null             $preferredStyleSchemes
     * @param array<string, mixed>|null $userInfo
     */
    public function __construct(
        // -- Required --
        public string $description,
        public string $organizationName,
        public string $teamIdentifier,
        public string $passTypeIdentifier,
        public int $formatVersion,
        public string $serialNumber,
        public PassTypeEnum $passType,
        public PassStructure $structure,

        // -- Barcodes --
        public array $barcodes = [],

        // -- Associated apps --
        public array $associatedStoreIdentifiers = [],
        public ?string $appLaunchURL = null,

        // -- Web service --
        public ?string $webServiceURL = null,
        public ?string $authenticationToken = null,

        // -- NFC --
        public ?Nfc $nfc = null,

        // -- Visual appearance --
        public ?Color $backgroundColor = null,
        public ?Color $foregroundColor = null,
        public ?Color $labelColor = null,
        public ?string $logoText = null,
        public ?bool $suppressStripShine = null,

        // -- Relevance --
        public ?array $locations = null,
        public ?array $beacons = null,
        public ?string $relevantDate = null,
        public ?array $relevantDates = null,
        public ?float $maxDistance = null,

        // -- Expiration --
        public ?string $expirationDate = null,
        public ?bool $voided = null,

        // -- Grouping --
        public ?string $groupingIdentifier = null,

        // -- Sharing --
        public ?bool $sharingProhibited = null,

        // -- Semantic tags --
        public ?SemanticTags $semantics = null,

        // -- Style schemes (iOS 26+) --
        public ?array $preferredStyleSchemes = null,

        // -- Developer payload --
        public ?array $userInfo = null,
    ) {
    }
}
