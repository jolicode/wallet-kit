<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Tests\Pass\Android\Normalizer;

use Jolicode\WalletKit\Common\Color;
use Jolicode\WalletKit\Pass\Android\Model\Shared\Barcode;
use Jolicode\WalletKit\Pass\Android\Model\Shared\BarcodeRenderEncodingEnum;
use Jolicode\WalletKit\Pass\Android\Model\Shared\BarcodeTypeEnum;
use Jolicode\WalletKit\Pass\Android\Model\Shared\GroupingInfo;
use Jolicode\WalletKit\Pass\Android\Model\Shared\Image;
use Jolicode\WalletKit\Pass\Android\Model\Shared\ImageUri;
use Jolicode\WalletKit\Pass\Android\Model\Shared\LocalizedString;
use Jolicode\WalletKit\Pass\Android\Model\Shared\Message;
use Jolicode\WalletKit\Pass\Android\Model\Shared\MessageTypeEnum;
use Jolicode\WalletKit\Pass\Android\Model\Shared\Money;
use Jolicode\WalletKit\Pass\Android\Model\Shared\PassConstraints;
use Jolicode\WalletKit\Pass\Android\Model\Shared\ReviewStatusEnum;
use Jolicode\WalletKit\Pass\Android\Model\Shared\ScreenshotEligibilityEnum;
use Jolicode\WalletKit\Pass\Android\Model\Shared\StateEnum;
use Jolicode\WalletKit\Pass\Android\Model\Shared\TranslatedString;
use Jolicode\WalletKit\Pass\Android\Model\Transit\ActivationStateEnum;
use Jolicode\WalletKit\Pass\Android\Model\Transit\ActivationStatus;
use Jolicode\WalletKit\Pass\Android\Model\Transit\ConcessionCategoryEnum;
use Jolicode\WalletKit\Pass\Android\Model\Transit\FareClassEnum;
use Jolicode\WalletKit\Pass\Android\Model\Transit\PassengerTypeEnum;
use Jolicode\WalletKit\Pass\Android\Model\Transit\PurchaseDetails;
use Jolicode\WalletKit\Pass\Android\Model\Transit\TicketCost;
use Jolicode\WalletKit\Pass\Android\Model\Transit\TicketLeg;
use Jolicode\WalletKit\Pass\Android\Model\Transit\TicketRestrictions;
use Jolicode\WalletKit\Pass\Android\Model\Transit\TicketSeat;
use Jolicode\WalletKit\Pass\Android\Model\Transit\TicketStatusEnum;
use Jolicode\WalletKit\Pass\Android\Model\Transit\TransitClass;
use Jolicode\WalletKit\Pass\Android\Model\Transit\TransitObject;
use Jolicode\WalletKit\Pass\Android\Model\Transit\TransitTypeEnum;
use Jolicode\WalletKit\Pass\Android\Model\Transit\TripTypeEnum;
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

final class TransitNormalizerTest extends TestCase
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

    public function testTransitPass(): void
    {
        $class = new TransitClass(
            id: 'transit-class-1',
            issuerName: 'Metro Transit',
            reviewStatus: ReviewStatusEnum::APPROVED,
            transitType: TransitTypeEnum::RAIL,
            localizedIssuerName: new LocalizedString(new TranslatedString('en', 'Metro Transit')),
            transitOperatorName: new LocalizedString(new TranslatedString('en', 'Metro Rail')),
            logo: new Image(new ImageUri('https://example.com/metro-logo.png')),
            hexBackgroundColor: Color::fromHex('#004D40'),
            countryCode: 'US',
            heroImage: new Image(new ImageUri('https://example.com/metro-hero.png')),
            enableSingleLegItinerary: true,
            messages: [new Message('Service Alert', 'Delays on Line 3', 'msg-1', MessageTypeEnum::TEXT_AND_NOTIFY)],
        );

        $ticketSeat = new TicketSeat(
            fareClass: FareClassEnum::FIRST,
            coach: '3',
            seat: '12A',
            seatAssignment: new LocalizedString(new TranslatedString('en', 'Coach 3, Seat 12A')),
        );

        $ticketLeg = new TicketLeg(
            originStationCode: 'UNION',
            originName: new LocalizedString(new TranslatedString('en', 'Union Station')),
            destinationStationCode: 'GRAND',
            destinationName: new LocalizedString(new TranslatedString('en', 'Grand Central')),
            departureDateTime: '2025-09-10T08:30:00',
            arrivalDateTime: '2025-09-10T11:45:00',
            fareName: new LocalizedString(new TranslatedString('en', 'Peak Single')),
            carriage: '3',
            platform: '5A',
            zone: 'Zone 1',
            ticketSeat: $ticketSeat,
            transitOperatorName: new LocalizedString(new TranslatedString('en', 'Metro Rail')),
        );

        $ticketCost = new TicketCost(
            faceValue: new Money('2500000', 'USD'),
            purchasePrice: new Money('2250000', 'USD'),
            discountMessage: new LocalizedString(new TranslatedString('en', '10% off')),
        );

        $purchaseDetails = new PurchaseDetails(
            purchaseReceiptNumber: 'RCP-001',
            purchaseDateTime: '2025-09-01T14:00:00Z',
            accountId: 'ACC-12345',
            confirmationCode: 'TRNST-001',
            ticketCost: $ticketCost,
        );

        $ticketRestrictions = new TicketRestrictions(
            routeRestrictions: new LocalizedString(new TranslatedString('en', 'Valid on express trains only')),
            timeRestrictions: new LocalizedString(new TranslatedString('en', 'Peak hours only')),
        );

        $object = new TransitObject(
            id: 'transit-object-1',
            classId: 'transit-class-1',
            state: StateEnum::ACTIVE,
            tripType: TripTypeEnum::ONE_WAY,
            ticketNumber: 'TKT-METRO-001',
            passengerType: PassengerTypeEnum::SINGLE_PASSENGER,
            passengerNames: 'John Doe',
            tripId: 'TRIP-001',
            ticketStatus: TicketStatusEnum::USED,
            concessionCategory: ConcessionCategoryEnum::ADULT,
            ticketRestrictions: $ticketRestrictions,
            purchaseDetails: $purchaseDetails,
            ticketLeg: $ticketLeg,
            ticketLegs: [$ticketLeg],
            hexBackgroundColor: Color::fromHex('#004D40'),
            barcode: new Barcode(BarcodeTypeEnum::QR_CODE, 'TRANSIT001', 'Metro Ticket', BarcodeRenderEncodingEnum::UTF_8),
            activationStatus: new ActivationStatus(ActivationStateEnum::ACTIVATED),
            heroImage: new Image(new ImageUri('https://example.com/metro-hero.png')),
            groupingInfo: new GroupingInfo('transit-group', 1),
            passConstraints: new PassConstraints(ScreenshotEligibilityEnum::ELIGIBLE),
        );

        $classData = $this->serializer->normalize($class);
        $objectData = $this->serializer->normalize($object);

        self::assertSame('transit-class-1', $classData['id']);
        self::assertSame('RAIL', $classData['transitType']);
        self::assertSame('Metro Transit', $classData['issuerName']);

        self::assertSame('transit-object-1', $objectData['id']);
        self::assertSame('ONE_WAY', $objectData['tripType']);
        self::assertSame('TKT-METRO-001', $objectData['ticketNumber']);
        self::assertSame('SINGLE_PASSENGER', $objectData['passengerType']);
        self::assertSame('UNION', $objectData['ticketLeg']['originStationCode']);
        self::assertSame('FIRST', $objectData['ticketLeg']['ticketSeat']['fareClass']);
        self::assertSame('12A', $objectData['ticketLeg']['ticketSeat']['seat']);
        self::assertSame('2500000', $objectData['purchaseDetails']['ticketCost']['faceValue']['micros']);
        self::assertSame('Valid on express trains only', $objectData['ticketRestrictions']['routeRestrictions']['defaultValue']['value']);
        self::assertSame('ACTIVATED', $objectData['activationStatus']['state']);
        self::assertSame('QR_CODE', $objectData['barcode']['type']);
    }
}
