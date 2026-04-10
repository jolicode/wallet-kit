<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Builder\Flight;

use Jolicode\WalletKit\Builder\AbstractWalletBuilder;
use Jolicode\WalletKit\Builder\BuiltWalletPass;
use Jolicode\WalletKit\Builder\GoogleVerticalEnum;
use Jolicode\WalletKit\Builder\GoogleWalletPair;
use Jolicode\WalletKit\Builder\WalletPlatformContext;
use Jolicode\WalletKit\Pass\Android\Model\Flight\AirportInfo;
use Jolicode\WalletKit\Pass\Android\Model\Flight\BoardingAndSeatingInfo;
use Jolicode\WalletKit\Pass\Android\Model\Flight\FlightClass;
use Jolicode\WalletKit\Pass\Android\Model\Flight\FlightHeader;
use Jolicode\WalletKit\Pass\Android\Model\Flight\FlightObject;
use Jolicode\WalletKit\Pass\Android\Model\Flight\ReservationInfo;
use Jolicode\WalletKit\Pass\Apple\Model\Field;
use Jolicode\WalletKit\Pass\Apple\Model\PassStructure;
use Jolicode\WalletKit\Pass\Apple\Model\PassTypeEnum;
use Jolicode\WalletKit\Pass\Apple\Model\TransitTypeEnum;
use Jolicode\WalletKit\Pass\Samsung\Model\BoardingPass\BoardingPassAttributes;
use Jolicode\WalletKit\Pass\Samsung\Model\Shared\CardSubTypeEnum;
use Jolicode\WalletKit\Pass\Samsung\Model\Shared\CardTypeEnum;

final class FlightWalletBuilder extends AbstractWalletBuilder
{
    private ?BoardingAndSeatingInfo $boardingAndSeatingInfo = null;

    public function __construct(
        WalletPlatformContext $context,
        private readonly string $passengerName,
        private readonly ReservationInfo $reservationInfo,
        private readonly FlightHeader $flightHeader,
        private readonly AirportInfo $origin,
        private readonly AirportInfo $destination,
    ) {
        parent::__construct($context);
    }

    public function withBoardingAndSeatingInfo(?BoardingAndSeatingInfo $info): self
    {
        $this->boardingAndSeatingInfo = $info;

        return $this;
    }

    public function build(): BuiltWalletPass
    {
        $flightNumber = $this->flightHeader->flightNumber ?? '';
        $originCode = $this->origin->airportIataCode ?? '';
        $destCode = $this->destination->airportIataCode ?? '';

        $headerFields = '' !== $flightNumber
            ? [new Field(key: 'flight', value: $flightNumber, label: 'Flight')]
            : [];

        $structure = new PassStructure(
            headerFields: $headerFields,
            primaryFields: [
                new Field(key: 'origin', value: $originCode, label: 'From'),
                new Field(key: 'destination', value: $destCode, label: 'To'),
            ],
            secondaryFields: [
                new Field(key: 'passenger', value: $this->passengerName, label: 'Passenger'),
            ],
            transitType: TransitTypeEnum::AIR,
        );

        $applePass = $this->context->hasApple()
            ? $this->createApplePass(PassTypeEnum::BOARDING_PASS, $structure)
            : null;

        $googlePair = null;
        if ($this->context->hasGoogle()) {
            $g = $this->context->google;
            $flightClass = new FlightClass(
                id: $g->classId,
                issuerName: $this->context->googleIssuerName(),
                reviewStatus: $this->resolvedGoogleReviewStatus(),
                origin: $this->origin,
                destination: $this->destination,
                flightHeader: $this->flightHeader,
                hexBackgroundColor: $this->resolvedGoogleHex(),
                linksModuleData: $this->common->linksModuleData,
                appLinkData: $this->common->appLinkData,
            );

            $flightObject = new FlightObject(
                id: $g->objectId,
                classId: $g->classId,
                state: $this->resolvedGoogleObjectState(),
                passengerName: $this->passengerName,
                reservationInfo: $this->reservationInfo,
                boardingAndSeatingInfo: $this->boardingAndSeatingInfo,
                barcode: $this->primaryGoogleBarcode(),
                hexBackgroundColor: $this->resolvedGoogleHex(),
                validTimeInterval: $this->common->validTimeInterval,
                linksModuleData: $this->common->linksModuleData,
                appLinkData: $this->common->appLinkData,
                groupingInfo: $this->resolvedGoogleGrouping(),
            );

            $googlePair = new GoogleWalletPair(GoogleVerticalEnum::FLIGHT, $flightClass, $flightObject);
        }

        $samsungCard = null;
        if ($this->context->hasSamsung()) {
            $s = $this->context->samsung;
            $attributes = new BoardingPassAttributes(
                title: 'Flight ' . ($this->flightHeader->flightNumber ?? ''),
                providerName: $this->context->hasApple() ? $this->context->apple->organizationName : ($this->context->hasGoogle() ? $this->context->googleIssuerName() : ''),
                bgColor: $this->resolvedSamsungHexColor() ?? '#000000',
                appLinkLogo: $s->appLinkLogo ?? '',
                appLinkName: $s->appLinkName ?? '',
                appLinkData: $s->appLinkData ?? '',
                user: $this->passengerName,
                vehicleNumber: $this->flightHeader->flightNumber,
                departCode: $this->origin->airportIataCode,
                arriveCode: $this->destination->airportIataCode,
                departTerminal: $this->origin->terminal,
                arriveTerminal: $this->destination->terminal,
                gate: $this->origin->gate,
                reservationNumber: $this->reservationInfo->confirmationCode,
                barcode: $this->primarySamsungBarcode(),
            );
            $samsungCard = $this->createSamsungCard(CardTypeEnum::BOARDING_PASS, CardSubTypeEnum::AIRLINES, $attributes);
        }

        return new BuiltWalletPass($applePass, $googlePair, $samsungCard);
    }
}
