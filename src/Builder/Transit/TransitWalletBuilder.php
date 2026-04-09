<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Builder\Transit;

use Jolicode\WalletKit\Builder\AbstractWalletBuilder;
use Jolicode\WalletKit\Builder\BuiltWalletPass;
use Jolicode\WalletKit\Builder\GoogleVerticalEnum;
use Jolicode\WalletKit\Builder\GoogleWalletPair;
use Jolicode\WalletKit\Builder\WalletPlatformContext;
use Jolicode\WalletKit\Common\Color;
use Jolicode\WalletKit\Pass\Android\Model\Transit\TransitClass;
use Jolicode\WalletKit\Pass\Android\Model\Transit\TransitObject;
use Jolicode\WalletKit\Pass\Android\Model\Transit\TransitTypeEnum as GoogleTransitTypeEnum;
use Jolicode\WalletKit\Pass\Android\Model\Transit\TripTypeEnum;
use Jolicode\WalletKit\Pass\Apple\Model\Field;
use Jolicode\WalletKit\Pass\Apple\Model\PassStructure;
use Jolicode\WalletKit\Pass\Apple\Model\PassTypeEnum;
use Jolicode\WalletKit\Pass\Apple\Model\TransitTypeEnum;
use Jolicode\WalletKit\Pass\Samsung\Model\BoardingPass\BoardingPassAttributes;
use Jolicode\WalletKit\Pass\Samsung\Model\Shared\CardTypeEnum;

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

        $applePass = $this->context->hasApple()
            ? $this->createApplePass(PassTypeEnum::BOARDING_PASS, $structure)
            : null;

        $googlePair = null;
        if ($this->context->hasGoogle()) {
            $g = $this->context->google;

            $transitClass = new TransitClass(
                id: $g->classId,
                issuerName: $this->context->googleIssuerName(),
                reviewStatus: $this->resolvedGoogleReviewStatus(),
                transitType: $this->googleTransitType,
                hexBackgroundColor: $this->resolvedBackgroundColor(),
                linksModuleData: $this->common->linksModuleData,
                appLinkData: $this->common->appLinkData,
            );

            $transitObject = new TransitObject(
                id: $g->objectId,
                classId: $g->classId,
                state: $this->resolvedGoogleObjectState(),
                tripType: $this->tripType,
                ticketNumber: $this->ticketNumber,
                barcode: $this->primaryGoogleBarcode(),
                hexBackgroundColor: $this->resolvedBackgroundColor(),
                validTimeInterval: $this->common->validTimeInterval,
                linksModuleData: $this->common->linksModuleData,
                appLinkData: $this->common->appLinkData,
                groupingInfo: $this->resolvedGoogleGrouping(),
            );

            $googlePair = new GoogleWalletPair(GoogleVerticalEnum::TRANSIT, $transitClass, $transitObject);
        }

        $samsungCard = null;
        if ($this->context->hasSamsung()) {
            $s = $this->context->samsung;
            $samsungSubType = \Jolicode\WalletKit\Builder\Internal\SamsungBoardingPassSubTypeMapper::fromTransitType($this->googleTransitType);
            $attributes = new BoardingPassAttributes(
                title: 'Transit',
                providerName: $this->context->hasApple() ? $this->context->apple->organizationName : ($this->context->hasGoogle() ? $this->context->googleIssuerName() : ''),
                bgColor: $this->resolvedBackgroundColor() ?? Color::fromHex('#000000'),
                appLinkLogo: $s->appLinkLogo ?? '',
                appLinkName: $s->appLinkName ?? '',
                appLinkData: $s->appLinkData ?? '',
                reservationNumber: $this->ticketNumber,
                barcode: $this->primarySamsungBarcode(),
            );
            $samsungCard = $this->createSamsungCard(CardTypeEnum::BOARDING_PASS, $samsungSubType, $attributes);
        }

        return new BuiltWalletPass($applePass, $googlePair, $samsungCard);
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
