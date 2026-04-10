<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Tests\Pass\Android\Normalizer;

use Jolicode\WalletKit\Common\Color;
use Jolicode\WalletKit\Pass\Android\Model\EventTicket\EventReservationInfo;
use Jolicode\WalletKit\Pass\Android\Model\EventTicket\EventSeat;
use Jolicode\WalletKit\Pass\Android\Model\EventTicket\EventTicketClass;
use Jolicode\WalletKit\Pass\Android\Model\EventTicket\EventTicketObject;
use Jolicode\WalletKit\Pass\Android\Model\Shared\Barcode;
use Jolicode\WalletKit\Pass\Android\Model\Shared\BarcodeRenderEncodingEnum;
use Jolicode\WalletKit\Pass\Android\Model\Shared\BarcodeTypeEnum;
use Jolicode\WalletKit\Pass\Android\Model\Shared\GoogleDateTime;
use Jolicode\WalletKit\Pass\Android\Model\Shared\GroupingInfo;
use Jolicode\WalletKit\Pass\Android\Model\Shared\Image;
use Jolicode\WalletKit\Pass\Android\Model\Shared\ImageUri;
use Jolicode\WalletKit\Pass\Android\Model\Shared\LinksModuleData;
use Jolicode\WalletKit\Pass\Android\Model\Shared\LocalizedString;
use Jolicode\WalletKit\Pass\Android\Model\Shared\Message;
use Jolicode\WalletKit\Pass\Android\Model\Shared\MessageTypeEnum;
use Jolicode\WalletKit\Pass\Android\Model\Shared\Money;
use Jolicode\WalletKit\Pass\Android\Model\Shared\PassConstraints;
use Jolicode\WalletKit\Pass\Android\Model\Shared\ReviewStatusEnum;
use Jolicode\WalletKit\Pass\Android\Model\Shared\ScreenshotEligibilityEnum;
use Jolicode\WalletKit\Pass\Android\Model\Shared\SecurityAnimation;
use Jolicode\WalletKit\Pass\Android\Model\Shared\SecurityAnimationTypeEnum;
use Jolicode\WalletKit\Pass\Android\Model\Shared\StateEnum;
use Jolicode\WalletKit\Pass\Android\Model\Shared\TextModuleData;
use Jolicode\WalletKit\Pass\Android\Model\Shared\TimeInterval;
use Jolicode\WalletKit\Pass\Android\Model\Shared\TranslatedString;
use Jolicode\WalletKit\Pass\Android\Model\Shared\Uri;
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

final class EventTicketNormalizerTest extends TestCase
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

    public function testEventTicketPass(): void
    {
        $class = new EventTicketClass(
            id: 'event-class-1',
            issuerName: 'Concert Venue',
            eventName: 'Rock Concert 2025',
            reviewStatus: ReviewStatusEnum::APPROVED,
            localizedEventName: new LocalizedString(new TranslatedString('en', 'Rock Concert 2025')),
            eventId: 'EVT-001',
            hexBackgroundColor: Color::fromHex('#1A1A2E'),
            countryCode: 'US',
            logo: new Image(new ImageUri('https://example.com/logo.png')),
            heroImage: new Image(new ImageUri('https://example.com/hero.png')),
            venue: new LocalizedString(new TranslatedString('en', 'Madison Square Garden')),
            dateTime: new LocalizedString(new TranslatedString('en', '2025-07-20 20:00')),
            finePrint: 'No refunds. No exchanges.',
            localizedFinePrint: new LocalizedString(new TranslatedString('en', 'No refunds. No exchanges.')),
            confirmationCodeLabel: new LocalizedString(new TranslatedString('en', 'Confirmation Code')),
            customSeatLabel: new LocalizedString(new TranslatedString('en', 'Your Seat')),
            enableSmartTap: true,
            securityAnimation: new SecurityAnimation(SecurityAnimationTypeEnum::FOIL_SHIMMER),
            messages: [new Message('Event Info', 'Doors open at 7 PM', 'msg-1', MessageTypeEnum::TEXT_AND_NOTIFY)],
            textModulesData: [new TextModuleData('Info', 'Bring valid ID', 'text-1')],
            linksModuleData: new LinksModuleData([new Uri('https://venue.com', 'Venue Website', null, 'uri-1')]),
        );

        $object = new EventTicketObject(
            id: 'event-object-1',
            classId: 'event-class-1',
            state: StateEnum::ACTIVE,
            seatInfo: new EventSeat(
                seat: new LocalizedString(new TranslatedString('en', '42A')),
                row: new LocalizedString(new TranslatedString('en', 'R')),
                section: new LocalizedString(new TranslatedString('en', 'Floor')),
                gate: new LocalizedString(new TranslatedString('en', 'Gate 3')),
            ),
            reservationInfo: new EventReservationInfo('CONF-12345'),
            ticketHolderName: 'John Doe',
            ticketNumber: 'TKT-001',
            ticketType: new LocalizedString(new TranslatedString('en', 'VIP')),
            faceValue: new Money('15000000', 'USD'),
            groupingInfo: new GroupingInfo('event-group', 1),
            hexBackgroundColor: Color::fromHex('#1A1A2E'),
            barcode: new Barcode(BarcodeTypeEnum::QR_CODE, 'EVT1234567890', 'Event Ticket', BarcodeRenderEncodingEnum::UTF_8),
            messages: [new Message('Reminder', 'Concert is tomorrow!', 'msg-1')],
            validTimeInterval: new TimeInterval(new GoogleDateTime('2025-07-20T00:00:00Z'), new GoogleDateTime('2025-07-21T00:00:00Z')),
            heroImage: new Image(new ImageUri('https://example.com/hero.png')),
            passConstraints: new PassConstraints(ScreenshotEligibilityEnum::ELIGIBLE),
        );

        $classData = $this->serializer->normalize($class);
        $objectData = $this->serializer->normalize($object);

        self::assertSame('event-class-1', $classData['id']);
        self::assertSame('Rock Concert 2025', $classData['eventName']);
        self::assertSame('Madison Square Garden', $classData['venue']['defaultValue']['value']);

        self::assertSame('event-object-1', $objectData['id']);
        self::assertSame('42A', $objectData['seatInfo']['seat']['defaultValue']['value']);
        self::assertSame('CONF-12345', $objectData['reservationInfo']['confirmationCode']);
        self::assertSame('John Doe', $objectData['ticketHolderName']);
        self::assertSame('15000000', $objectData['faceValue']['micros']);
        self::assertSame('QR_CODE', $objectData['barcode']['type']);
    }
}
