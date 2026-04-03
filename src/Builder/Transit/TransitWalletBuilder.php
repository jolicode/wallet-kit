<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Builder\Transit;

use Jolicode\WalletKit\Builder\AbstractWalletBuilder;
use Jolicode\WalletKit\Builder\BuiltWalletPass;
use Jolicode\WalletKit\Builder\GoogleVerticalEnum;
use Jolicode\WalletKit\Builder\GoogleWalletPair;
use Jolicode\WalletKit\Builder\WalletPlatformContext;
use Jolicode\WalletKit\Pass\Android\Model\Transit\TransitClass;
use Jolicode\WalletKit\Pass\Android\Model\Transit\TransitObject;
use Jolicode\WalletKit\Pass\Android\Model\Transit\TransitTypeEnum as GoogleTransitTypeEnum;
use Jolicode\WalletKit\Pass\Android\Model\Transit\TripTypeEnum;
use Jolicode\WalletKit\Pass\Apple\Model\Field;
use Jolicode\WalletKit\Pass\Apple\Model\PassStructure;
use Jolicode\WalletKit\Pass\Apple\Model\PassTypeEnum;
use Jolicode\WalletKit\Pass\Apple\Model\TransitTypeEnum;

final class TransitWalletBuilder extends AbstractWalletBuilder
{
    private ?string $ticketNumber = null;

    public function __construct(
        WalletPlatformContext $context,
        private readonly GoogleTransitTypeEnum $googleTransitType,
        private readonly TripTypeEnum $tripType,
    ) {
        parent::__construct($context);
    }

    public function withTicketNumber(?string $ticketNumber): self
    {
        $this->ticketNumber = $ticketNumber;

        return $this;
    }

    public function build(): BuiltWalletPass
    {
        $primaryFields = [];
        if (null !== $this->ticketNumber && '' !== $this->ticketNumber) {
            $primaryFields[] = new Field(key: 'ticket', value: $this->ticketNumber, label: 'Ticket');
        }

        $structure = new PassStructure(
            primaryFields: $primaryFields,
            transitType: $this->appleTransitType(),
        );

        $applePass = $this->createApplePass(PassTypeEnum::BOARDING_PASS, $structure);

        $transitClass = new TransitClass(
            id: $this->context->googleClassId,
            issuerName: $this->context->appleOrganizationName,
            reviewStatus: $this->resolvedGoogleReviewStatus(),
            transitType: $this->googleTransitType,
            hexBackgroundColor: $this->resolvedGoogleHex(),
            linksModuleData: $this->common->linksModuleData,
            appLinkData: $this->common->appLinkData,
        );

        $transitObject = new TransitObject(
            id: $this->context->googleObjectId,
            classId: $this->context->googleClassId,
            state: $this->resolvedGoogleObjectState(),
            tripType: $this->tripType,
            ticketNumber: $this->ticketNumber,
            barcode: $this->primaryGoogleBarcode(),
            hexBackgroundColor: $this->resolvedGoogleHex(),
            validTimeInterval: $this->common->validTimeInterval,
            linksModuleData: $this->common->linksModuleData,
            appLinkData: $this->common->appLinkData,
            groupingInfo: $this->resolvedGoogleGrouping(),
        );

        return new BuiltWalletPass(
            $applePass,
            new GoogleWalletPair(GoogleVerticalEnum::TRANSIT, $transitClass, $transitObject),
        );
    }

    private function appleTransitType(): TransitTypeEnum
    {
        return match ($this->googleTransitType) {
            GoogleTransitTypeEnum::BUS => TransitTypeEnum::BUS,
            GoogleTransitTypeEnum::RAIL => TransitTypeEnum::TRAIN,
            GoogleTransitTypeEnum::TRAM => TransitTypeEnum::TRAIN,
            GoogleTransitTypeEnum::FERRY => TransitTypeEnum::BOAT,
            GoogleTransitTypeEnum::OTHER, GoogleTransitTypeEnum::UNSPECIFIED => TransitTypeEnum::GENERIC,
        };
    }
}
