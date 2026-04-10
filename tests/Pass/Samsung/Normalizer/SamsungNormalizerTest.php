<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Tests\Pass\Samsung\Normalizer;

use Jolicode\WalletKit\Pass\Samsung\Model\BoardingPass\BoardingPassAttributes;
use Jolicode\WalletKit\Pass\Samsung\Model\Card;
use Jolicode\WalletKit\Pass\Samsung\Model\CardData;
use Jolicode\WalletKit\Pass\Samsung\Model\Coupon\CouponAttributes;
use Jolicode\WalletKit\Pass\Samsung\Model\EventTicket\EventTicketAttributes;
use Jolicode\WalletKit\Pass\Samsung\Model\Generic\GenericAttributes;
use Jolicode\WalletKit\Pass\Samsung\Model\GiftCard\GiftCardAttributes;
use Jolicode\WalletKit\Pass\Samsung\Model\Localization;
use Jolicode\WalletKit\Pass\Samsung\Model\Loyalty\LoyaltyAttributes;
use Jolicode\WalletKit\Pass\Samsung\Model\Shared\CardSubTypeEnum;
use Jolicode\WalletKit\Pass\Samsung\Model\Shared\CardTypeEnum;
use Jolicode\WalletKit\Pass\Samsung\Model\Shared\Location;
use Jolicode\WalletKit\Pass\Samsung\Model\Shared\SamsungBarcode;
use Jolicode\WalletKit\Pass\Samsung\Model\Shared\SamsungImage;
use Jolicode\WalletKit\Pass\Samsung\Model\Shared\SerialTypeEnum;
use Jolicode\WalletKit\Pass\Samsung\Normalizer\BoardingPass\BoardingPassAttributesNormalizer;
use Jolicode\WalletKit\Pass\Samsung\Normalizer\CardDataNormalizer;
use Jolicode\WalletKit\Pass\Samsung\Normalizer\CardNormalizer;
use Jolicode\WalletKit\Pass\Samsung\Normalizer\Coupon\CouponAttributesNormalizer;
use Jolicode\WalletKit\Pass\Samsung\Normalizer\EventTicket\EventTicketAttributesNormalizer;
use Jolicode\WalletKit\Pass\Samsung\Normalizer\Generic\GenericAttributesNormalizer;
use Jolicode\WalletKit\Pass\Samsung\Normalizer\GiftCard\GiftCardAttributesNormalizer;
use Jolicode\WalletKit\Pass\Samsung\Normalizer\LocalizationNormalizer;
use Jolicode\WalletKit\Pass\Samsung\Normalizer\Loyalty\LoyaltyAttributesNormalizer;
use Jolicode\WalletKit\Pass\Samsung\Normalizer\Shared\LocationNormalizer;
use Jolicode\WalletKit\Pass\Samsung\Normalizer\Shared\SamsungBarcodeNormalizer;
use Jolicode\WalletKit\Pass\Samsung\Normalizer\Shared\SamsungImageNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Serializer;

final class SamsungNormalizerTest extends TestCase
{
    private Serializer $serializer;

    protected function setUp(): void
    {
        $this->serializer = new Serializer([
            new SamsungImageNormalizer(),
            new SamsungBarcodeNormalizer(),
            new LocationNormalizer(),
            new BoardingPassAttributesNormalizer(),
            new EventTicketAttributesNormalizer(),
            new CouponAttributesNormalizer(),
            new GiftCardAttributesNormalizer(),
            new LoyaltyAttributesNormalizer(),
            new GenericAttributesNormalizer(),
            new LocalizationNormalizer(),
            new CardDataNormalizer(),
            new CardNormalizer(),
        ]);
    }

    public function testBoardingPassCard(): void
    {
        $barcode = new SamsungBarcode(SerialTypeEnum::QRCODE, 'BOARDING123', 'qrcode');
        $attributes = new BoardingPassAttributes(
            title: 'Flight ZZ412',
            providerName: 'Example Airlines',
            bgColor: '#1E3C5A',
            appLinkLogo: 'https://example.com/logo.png',
            appLinkName: 'Example Airlines',
            appLinkData: 'https://example.com',
            providerLogo: new SamsungImage('https://example.com/dark.png', 'https://example.com/light.png'),
            user: 'Jordan Smith',
            vehicleNumber: 'ZZ412',
            seatNumber: '14A',
            departCode: 'CDG',
            arriveCode: 'JFK',
            reservationNumber: 'XK4P2Q',
            barcode: $barcode,
            preventCapture: true,
        );

        $card = new Card(
            CardTypeEnum::BOARDING_PASS,
            CardSubTypeEnum::AIRLINES,
            [new CardData('ref-001', 1612660039000, 1612660039000, 'en', $attributes)],
        );

        $data = $this->serializer->normalize($card);

        self::assertSame('boardingpass', $data['card']['type']);
        self::assertSame('airlines', $data['card']['subType']);
        self::assertCount(1, $data['card']['data']);

        $cardData = $data['card']['data'][0];
        self::assertSame('ref-001', $cardData['refId']);
        self::assertSame(1612660039000, $cardData['createdAt']);
        self::assertSame('en', $cardData['language']);

        $attrs = $cardData['attributes'];
        self::assertSame('Flight ZZ412', $attrs['title']);
        self::assertSame('Example Airlines', $attrs['providerName']);
        self::assertSame('#1E3C5A', $attrs['bgColor']);
        self::assertSame('Jordan Smith', $attrs['user']);
        self::assertSame('ZZ412', $attrs['vehicleNumber']);
        self::assertSame('14A', $attrs['seatNumber']);
        self::assertSame('CDG', $attrs['departCode']);
        self::assertSame('JFK', $attrs['arriveCode']);
        self::assertSame('XK4P2Q', $attrs['reservationNumber']);
        self::assertSame('Y', $attrs['preventCaptureYn']);

        self::assertSame('QRCODE', $attrs['barcode']['serialType']);
        self::assertSame('BOARDING123', $attrs['barcode']['value']);

        self::assertSame('https://example.com/dark.png', $attrs['providerLogo']['darkUrl']);
        self::assertSame('https://example.com/light.png', $attrs['providerLogo']['lightUrl']);
    }

    public function testEventTicketCard(): void
    {
        $attributes = new EventTicketAttributes(
            title: 'Indie Fest 2026',
            providerName: 'Festival Inc.',
            issueDate: 1612660039000,
            reservationNumber: 'EVT-001',
            startDate: 1612660039000,
            noticeDesc: 'Doors open at 6pm',
            appLinkLogo: 'https://example.com/logo.png',
            appLinkName: 'Festival App',
            appLinkData: 'https://example.com',
            holderName: 'Sam Rivera',
            bgColor: '#FF6B35',
            locations: [new Location(48.8566, 2.3522, '123 Rue Example', 'Venue')],
            preventCapture: false,
            noNetworkSupport: true,
        );

        $card = new Card(
            CardTypeEnum::TICKET,
            CardSubTypeEnum::PERFORMANCES,
            [new CardData('ref-evt', 1612660039000, 1612660039000, 'en', $attributes)],
        );

        $data = $this->serializer->normalize($card);

        self::assertSame('ticket', $data['card']['type']);
        self::assertSame('performances', $data['card']['subType']);

        $attrs = $data['card']['data'][0]['attributes'];
        self::assertSame('Indie Fest 2026', $attrs['title']);
        self::assertSame('Sam Rivera', $attrs['holderName']);
        self::assertSame('#FF6B35', $attrs['bgColor']);
        self::assertSame('N', $attrs['preventCaptureYn']);
        self::assertSame('Y', $attrs['noNetworkSupportYn']);
        self::assertCount(1, $attrs['locations']);
        self::assertSame(48.8566, $attrs['locations'][0]['lat']);
        self::assertSame('Venue', $attrs['locations'][0]['name']);
    }

    public function testCouponCard(): void
    {
        $attributes = new CouponAttributes(
            title: '20% off',
            appLinkLogo: 'https://example.com/logo.png',
            appLinkName: 'Shop App',
            appLinkData: 'https://example.com',
            issueDate: 1612660039000,
            expiry: 1712660039000,
            brandName: 'Example Coffee',
            barcode: new SamsungBarcode(SerialTypeEnum::BARCODE, 'PROMO-2026'),
            editable: false,
            deletable: true,
            displayRedeemButton: true,
            notification: true,
        );

        $data = $this->serializer->normalize(
            new Card(CardTypeEnum::COUPON, CardSubTypeEnum::OTHERS, [new CardData('ref-coup', 1612660039000, 1612660039000, 'en', $attributes)])
        );

        $attrs = $data['card']['data'][0]['attributes'];
        self::assertSame('coupon', $data['card']['type']);
        self::assertSame('20% off', $attrs['title']);
        self::assertSame('Example Coffee', $attrs['brandName']);
        self::assertSame('N', $attrs['editableYn']);
        self::assertSame('Y', $attrs['deletableYn']);
        self::assertSame('Y', $attrs['displayRedeemButtonYn']);
        self::assertSame('Y', $attrs['notificationYn']);
        self::assertSame('BARCODE', $attrs['barcode']['serialType']);
    }

    public function testGiftCardCard(): void
    {
        $attributes = new GiftCardAttributes(
            title: 'Gift Card',
            providerName: 'Example Store',
            appLinkLogo: 'https://example.com/logo.png',
            appLinkName: 'Store App',
            appLinkData: 'https://example.com',
            barcode: new SamsungBarcode(SerialTypeEnum::QRCODE, '6034932523842700'),
            amount: '$50.00',
            balance: '$42.50',
            merchantName: 'Example Store',
        );

        $data = $this->serializer->normalize(
            new Card(CardTypeEnum::GIFT_CARD, CardSubTypeEnum::OTHERS, [new CardData('ref-gift', 1612660039000, 1612660039000, 'en', $attributes)])
        );

        $attrs = $data['card']['data'][0]['attributes'];
        self::assertSame('giftcard', $data['card']['type']);
        self::assertSame('Gift Card', $attrs['title']);
        self::assertSame('$50.00', $attrs['amount']);
        self::assertSame('$42.50', $attrs['balance']);
        self::assertSame('Example Store', $attrs['merchantName']);
    }

    public function testLoyaltyCard(): void
    {
        $attributes = new LoyaltyAttributes(
            title: 'Gold Rewards',
            providerName: 'Rewards Inc.',
            appLinkLogo: 'https://example.com/logo.png',
            appLinkName: 'Rewards App',
            appLinkData: 'https://example.com',
            barcode: new SamsungBarcode(SerialTypeEnum::QRCODE, 'GLD-991122'),
            merchantName: 'Rewards Inc.',
            balance: '1,250 pts',
        );

        $data = $this->serializer->normalize(
            new Card(CardTypeEnum::LOYALTY, CardSubTypeEnum::OTHERS, [new CardData('ref-loy', 1612660039000, 1612660039000, 'en', $attributes)])
        );

        $attrs = $data['card']['data'][0]['attributes'];
        self::assertSame('loyalty', $data['card']['type']);
        self::assertSame('Gold Rewards', $attrs['title']);
        self::assertSame('1,250 pts', $attrs['balance']);
    }

    public function testGenericCard(): void
    {
        $attributes = new GenericAttributes(
            title: 'Membership Card',
            providerName: 'Gym Corp',
            startDate: 1612660039000,
            noticeDesc: 'Valid for 1 year',
            appLinkLogo: 'https://example.com/logo.png',
            appLinkName: 'Gym App',
            appLinkData: 'https://example.com',
            subtitle: 'Gold Member',
            groupingId: 'membership-2026',
            serial1: new SamsungBarcode(SerialTypeEnum::QRCODE, 'MEM8842'),
            privacyMode: true,
        );

        $data = $this->serializer->normalize(
            new Card(CardTypeEnum::GENERIC, CardSubTypeEnum::OTHERS, [new CardData('ref-gen', 1612660039000, 1612660039000, 'en', $attributes)])
        );

        $attrs = $data['card']['data'][0]['attributes'];
        self::assertSame('generic', $data['card']['type']);
        self::assertSame('Membership Card', $attrs['title']);
        self::assertSame('Gold Member', $attrs['subtitle']);
        self::assertSame('membership-2026', $attrs['groupingId']);
        self::assertSame('Y', $attrs['privacyModeYn']);
        self::assertSame('QRCODE', $attrs['serial1']['serialType']);
    }

    public function testLocalization(): void
    {
        $enAttributes = new GenericAttributes(
            title: 'Membership',
            providerName: 'Gym',
            startDate: 1612660039000,
            noticeDesc: 'Valid',
            appLinkLogo: '',
            appLinkName: '',
            appLinkData: '',
        );

        $frAttributes = new GenericAttributes(
            title: 'Adhésion',
            providerName: 'Gym',
            startDate: 1612660039000,
            noticeDesc: 'Valide',
            appLinkLogo: '',
            appLinkName: '',
            appLinkData: '',
        );

        $card = new Card(
            CardTypeEnum::GENERIC,
            CardSubTypeEnum::OTHERS,
            [new CardData(
                'ref-loc',
                1612660039000,
                1612660039000,
                'en',
                $enAttributes,
                [new Localization('fr', $frAttributes)],
            )],
        );

        $data = $this->serializer->normalize($card);
        $cardData = $data['card']['data'][0];

        self::assertSame('Membership', $cardData['attributes']['title']);
        self::assertArrayHasKey('localization', $cardData);
        self::assertCount(1, $cardData['localization']);
        self::assertSame('fr', $cardData['localization'][0]['language']);
        self::assertSame('Adhésion', $cardData['localization'][0]['attributes']['title']);
    }
}
