<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Tests\Pass\Android\Normalizer;

use Jolicode\WalletKit\Pass\Android\Model\Flight\AirportInfo;
use Jolicode\WalletKit\Pass\Android\Model\Flight\BoardingAndSeatingInfo;
use Jolicode\WalletKit\Pass\Android\Model\Flight\BoardingDoorEnum;
use Jolicode\WalletKit\Pass\Android\Model\Flight\BoardingPolicyEnum;
use Jolicode\WalletKit\Pass\Android\Model\Flight\FlightCarrier;
use Jolicode\WalletKit\Pass\Android\Model\Flight\FlightClass;
use Jolicode\WalletKit\Pass\Android\Model\Flight\FlightHeader;
use Jolicode\WalletKit\Pass\Android\Model\Flight\FlightObject;
use Jolicode\WalletKit\Pass\Android\Model\Flight\FrequentFlyerInfo;
use Jolicode\WalletKit\Pass\Android\Model\Flight\ReservationInfo;
use Jolicode\WalletKit\Pass\Android\Model\Flight\SeatClassPolicyEnum;
use Jolicode\WalletKit\Pass\Android\Model\Shared\Barcode;
use Jolicode\WalletKit\Pass\Android\Model\Shared\BarcodeRenderEncodingEnum;
use Jolicode\WalletKit\Pass\Android\Model\Shared\BarcodeTypeEnum;
use Jolicode\WalletKit\Pass\Android\Model\Shared\GroupingInfo;
use Jolicode\WalletKit\Pass\Android\Model\Shared\Image;
use Jolicode\WalletKit\Pass\Android\Model\Shared\ImageUri;
use Jolicode\WalletKit\Pass\Android\Model\Shared\LocalizedString;
use Jolicode\WalletKit\Pass\Android\Model\Shared\Message;
use Jolicode\WalletKit\Pass\Android\Model\Shared\MessageTypeEnum;
use Jolicode\WalletKit\Pass\Android\Model\Shared\ReviewStatusEnum;
use Jolicode\WalletKit\Pass\Android\Model\Shared\StateEnum;
use Jolicode\WalletKit\Pass\Android\Model\Shared\TranslatedString;
use Jolicode\WalletKit\Pass\Android\Normalizer\EventTicket\EventReservationInfoNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\EventTicket\EventSeatNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\EventTicket\EventTicketClassNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\EventTicket\EventTicketObjectNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Flight\AirportInfoNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Flight\BoardingAndSeatingInfoNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Flight\FlightCarrierNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Flight\FlightClassNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Flight\FlightHeaderNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Flight\FlightObjectNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Flight\FrequentFlyerInfoNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Flight\ReservationInfoNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Generic\ExpiryNotificationNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Generic\GenericClassNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Generic\GenericObjectNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Generic\NotificationsNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Generic\UpcomingNotificationNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\GiftCard\GiftCardClassNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\GiftCard\GiftCardObjectNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Loyalty\LoyaltyClassNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Loyalty\LoyaltyObjectNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Loyalty\LoyaltyPointsBalanceNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Loyalty\LoyaltyPointsNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Offer\OfferClassNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Offer\OfferObjectNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Shared\AppLinkDataNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Shared\AppLinkInfoNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Shared\AppTargetNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Shared\BarcodeNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Shared\CallbackOptionsNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Shared\GoogleDateTimeNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Shared\GroupingInfoNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Shared\ImageModuleDataNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Shared\ImageNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Shared\ImageUriNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Shared\LatLongPointNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Shared\LinksModuleDataNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Shared\LocalizedStringNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Shared\MessageNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Shared\MoneyNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Shared\PassConstraintsNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Shared\RotatingBarcodeNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Shared\SecurityAnimationNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Shared\TextModuleDataNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Shared\TimeIntervalNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Shared\TranslatedStringNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Shared\UriNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Transit\ActivationStatusNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Transit\PurchaseDetailsNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Transit\TicketCostNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Transit\TicketLegNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Transit\TicketRestrictionsNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Transit\TicketSeatNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Transit\TransitClassNormalizer;
use Jolicode\WalletKit\Pass\Android\Normalizer\Transit\TransitObjectNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Serializer;

final class FlightNormalizerTest extends TestCase
{
    private Serializer $serializer;

    protected function setUp(): void
    {
        $this->serializer = new Serializer([
            new TranslatedStringNormalizer(),
            new LocalizedStringNormalizer(),
            new ImageUriNormalizer(),
            new ImageNormalizer(),
            new BarcodeNormalizer(),
            new RotatingBarcodeNormalizer(),
            new MoneyNormalizer(),
            new GoogleDateTimeNormalizer(),
            new TimeIntervalNormalizer(),
            new MessageNormalizer(),
            new LatLongPointNormalizer(),
            new TextModuleDataNormalizer(),
            new UriNormalizer(),
            new LinksModuleDataNormalizer(),
            new ImageModuleDataNormalizer(),
            new GroupingInfoNormalizer(),
            new PassConstraintsNormalizer(),
            new CallbackOptionsNormalizer(),
            new SecurityAnimationNormalizer(),
            new AppTargetNormalizer(),
            new AppLinkInfoNormalizer(),
            new AppLinkDataNormalizer(),
            new ExpiryNotificationNormalizer(),
            new UpcomingNotificationNormalizer(),
            new NotificationsNormalizer(),
            new GenericClassNormalizer(),
            new GenericObjectNormalizer(),
            new LoyaltyPointsBalanceNormalizer(),
            new LoyaltyPointsNormalizer(),
            new LoyaltyClassNormalizer(),
            new LoyaltyObjectNormalizer(),
            new OfferClassNormalizer(),
            new OfferObjectNormalizer(),
            new GiftCardClassNormalizer(),
            new GiftCardObjectNormalizer(),
            new EventSeatNormalizer(),
            new EventReservationInfoNormalizer(),
            new EventTicketClassNormalizer(),
            new EventTicketObjectNormalizer(),
            new AirportInfoNormalizer(),
            new FlightCarrierNormalizer(),
            new FlightHeaderNormalizer(),
            new FrequentFlyerInfoNormalizer(),
            new ReservationInfoNormalizer(),
            new BoardingAndSeatingInfoNormalizer(),
            new FlightClassNormalizer(),
            new FlightObjectNormalizer(),
            new TicketSeatNormalizer(),
            new TicketLegNormalizer(),
            new TicketRestrictionsNormalizer(),
            new TicketCostNormalizer(),
            new PurchaseDetailsNormalizer(),
            new ActivationStatusNormalizer(),
            new TransitClassNormalizer(),
            new TransitObjectNormalizer(),
        ]);
    }

    public function testFlightPass(): void
    {
        $carrier = new FlightCarrier(
            carrierIataCode: 'AF',
            airlineName: new LocalizedString(new TranslatedString('en', 'Air France')),
            airlineLogo: new Image(new ImageUri('https://example.com/af-logo.png')),
        );

        $flightHeader = new FlightHeader(
            carrier: $carrier,
            flightNumber: '123',
            flightNumberDisplayOverride: 'AF 123',
        );

        $origin = new AirportInfo(
            airportIataCode: 'CDG',
            airportNameOverride: new LocalizedString(new TranslatedString('en', 'Paris Charles de Gaulle')),
            terminal: '2E',
            gate: 'K32',
        );

        $destination = new AirportInfo(
            airportIataCode: 'JFK',
            airportNameOverride: new LocalizedString(new TranslatedString('en', 'New York JFK')),
            terminal: '1',
            gate: 'B21',
        );

        $class = new FlightClass(
            id: 'flight-class-1',
            issuerName: 'Air France',
            reviewStatus: ReviewStatusEnum::APPROVED,
            origin: $origin,
            destination: $destination,
            flightHeader: $flightHeader,
            localScheduledDepartureDateTime: '2025-08-15T10:00',
            localScheduledArrivalDateTime: '2025-08-15T14:30',
            localBoardingDateTime: '2025-08-15T09:30',
            boardingPolicy: BoardingPolicyEnum::ZONE_BASED,
            seatClassPolicy: SeatClassPolicyEnum::CABIN_BASED,
            hexBackgroundColor: '#003366',
            countryCode: 'FR',
            heroImage: new Image(new ImageUri('https://example.com/flight-hero.png')),
            messages: [new Message('Flight Update', 'Gate changed to K32', 'msg-1', MessageTypeEnum::TEXT_AND_NOTIFY)],
        );

        $reservationInfo = new ReservationInfo(
            confirmationCode: 'ABC123',
            eticketNumber: '0571234567890',
            frequentFlyerInfo: new FrequentFlyerInfo(
                frequentFlyerProgramName: new LocalizedString(new TranslatedString('en', 'Flying Blue')),
                frequentFlyerNumber: 'FB123456',
            ),
        );

        $boardingInfo = new BoardingAndSeatingInfo(
            boardingGroup: 'Group 2',
            seatNumber: '14A',
            seatClass: 'Economy',
            boardingDoor: BoardingDoorEnum::FRONT,
            sequenceNumber: '042',
        );

        $object = new FlightObject(
            id: 'flight-object-1',
            classId: 'flight-class-1',
            state: StateEnum::ACTIVE,
            passengerName: 'DUPONT/JEAN',
            reservationInfo: $reservationInfo,
            boardingAndSeatingInfo: $boardingInfo,
            barcode: new Barcode(BarcodeTypeEnum::QR_CODE, 'M1DUPONT/JEAN  ABC123 CDGJFK AF 0123', null, BarcodeRenderEncodingEnum::UTF_8),
            hexBackgroundColor: '#003366',
            heroImage: new Image(new ImageUri('https://example.com/flight-hero.png')),
            groupingInfo: new GroupingInfo('flight-group', 1),
        );

        $classData = $this->serializer->normalize($class);
        $objectData = $this->serializer->normalize($object);

        self::assertSame('flight-class-1', $classData['id']);
        self::assertSame('CDG', $classData['origin']['airportIataCode']);
        self::assertSame('JFK', $classData['destination']['airportIataCode']);
        self::assertSame('AF', $classData['flightHeader']['carrier']['carrierIataCode']);
        self::assertSame('123', $classData['flightHeader']['flightNumber']);
        self::assertSame('ZONE_BASED', $classData['boardingPolicy']);

        self::assertSame('flight-object-1', $objectData['id']);
        self::assertSame('DUPONT/JEAN', $objectData['passengerName']);
        self::assertSame('ABC123', $objectData['reservationInfo']['confirmationCode']);
        self::assertSame('FB123456', $objectData['reservationInfo']['frequentFlyerInfo']['frequentFlyerNumber']);
        self::assertSame('14A', $objectData['boardingAndSeatingInfo']['seatNumber']);
        self::assertSame('FRONT', $objectData['boardingAndSeatingInfo']['boardingDoor']);
    }
}
