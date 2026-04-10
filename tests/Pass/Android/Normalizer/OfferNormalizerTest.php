<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Tests\Pass\Android\Normalizer;

use Jolicode\WalletKit\Common\Color;
use Jolicode\WalletKit\Pass\Android\Model\Offer\OfferClass;
use Jolicode\WalletKit\Pass\Android\Model\Offer\OfferObject;
use Jolicode\WalletKit\Pass\Android\Model\Offer\RedemptionChannelEnum;
use Jolicode\WalletKit\Pass\Android\Model\Shared\AppLinkData;
use Jolicode\WalletKit\Pass\Android\Model\Shared\AppLinkInfo;
use Jolicode\WalletKit\Pass\Android\Model\Shared\AppTarget;
use Jolicode\WalletKit\Pass\Android\Model\Shared\Barcode;
use Jolicode\WalletKit\Pass\Android\Model\Shared\BarcodeRenderEncodingEnum;
use Jolicode\WalletKit\Pass\Android\Model\Shared\BarcodeTypeEnum;
use Jolicode\WalletKit\Pass\Android\Model\Shared\CallbackOptions;
use Jolicode\WalletKit\Pass\Android\Model\Shared\GoogleDateTime;
use Jolicode\WalletKit\Pass\Android\Model\Shared\GroupingInfo;
use Jolicode\WalletKit\Pass\Android\Model\Shared\Image;
use Jolicode\WalletKit\Pass\Android\Model\Shared\ImageModuleData;
use Jolicode\WalletKit\Pass\Android\Model\Shared\ImageUri;
use Jolicode\WalletKit\Pass\Android\Model\Shared\LinksModuleData;
use Jolicode\WalletKit\Pass\Android\Model\Shared\LocalizedString;
use Jolicode\WalletKit\Pass\Android\Model\Shared\Message;
use Jolicode\WalletKit\Pass\Android\Model\Shared\MessageTypeEnum;
use Jolicode\WalletKit\Pass\Android\Model\Shared\MultipleDevicesAndHoldersAllowedStatusEnum;
use Jolicode\WalletKit\Pass\Android\Model\Shared\NfcConstraintEnum;
use Jolicode\WalletKit\Pass\Android\Model\Shared\PassConstraints;
use Jolicode\WalletKit\Pass\Android\Model\Shared\ReviewStatusEnum;
use Jolicode\WalletKit\Pass\Android\Model\Shared\RotatingBarcode;
use Jolicode\WalletKit\Pass\Android\Model\Shared\ScreenshotEligibilityEnum;
use Jolicode\WalletKit\Pass\Android\Model\Shared\SecurityAnimation;
use Jolicode\WalletKit\Pass\Android\Model\Shared\SecurityAnimationTypeEnum;
use Jolicode\WalletKit\Pass\Android\Model\Shared\StateEnum;
use Jolicode\WalletKit\Pass\Android\Model\Shared\TextModuleData;
use Jolicode\WalletKit\Pass\Android\Model\Shared\TimeInterval;
use Jolicode\WalletKit\Pass\Android\Model\Shared\TranslatedString;
use Jolicode\WalletKit\Pass\Android\Model\Shared\Uri;
use Jolicode\WalletKit\Pass\Android\Model\Shared\ViewUnlockRequirementEnum;
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

final class OfferNormalizerTest extends TestCase
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

    public function testOfferPass(): void
    {
        $image = new Image(new ImageUri('https://example.com/img.png'), new LocalizedString(new TranslatedString('en', 'Image description')));
        $linksModuleData = new LinksModuleData([new Uri('https://example.com', 'Example', null, 'uri-1')]);
        $textModulesData = [new TextModuleData('Header', 'Body', 'text-1')];
        $imageModulesData = [new ImageModuleData(new Image(new ImageUri('https://example.com/module.png')), 'img-1')];
        $messages = [new Message('Title', 'Body', 'msg-1', MessageTypeEnum::TEXT)];
        $appLinkData = new AppLinkData(
            androidAppLinkInfo: new AppLinkInfo(
                title: new LocalizedString(new TranslatedString('en', 'Open App')),
                appTarget: new AppTarget(new Uri('https://example.com/app'), 'com.example.app'),
            ),
        );

        $class = new OfferClass(
            id: 'offer-class-001',
            issuerName: 'Deal Store',
            title: '20% Off Everything',
            provider: 'Deal Store Inc.',
            redemptionChannel: RedemptionChannelEnum::BOTH,
            reviewStatus: ReviewStatusEnum::APPROVED,
            titleImage: $image,
            wideTitleImage: new Image(new ImageUri('https://example.com/wide-title.png')),
            details: 'Get 20% off on all items in store and online.',
            finePrint: 'Cannot be combined with other offers. Valid until Dec 31.',
            helpUri: new Uri('https://example.com/help', 'Help Center'),
            localizedTitle: new LocalizedString(new TranslatedString('en', '20% Off Everything')),
            localizedProvider: new LocalizedString(new TranslatedString('en', 'Deal Store Inc.')),
            localizedDetails: new LocalizedString(new TranslatedString('en', 'Get 20% off on all items in store and online.')),
            localizedFinePrint: new LocalizedString(new TranslatedString('en', 'Cannot be combined with other offers.')),
            shortTitle: '20% Off',
            localizedShortTitle: new LocalizedString(new TranslatedString('en', '20% Off')),
            hexBackgroundColor: Color::fromHex('#FF6B35'),
            localizedIssuerName: new LocalizedString(new TranslatedString('en', 'Deal Store')),
            countryCode: 'US',
            heroImage: new Image(new ImageUri('https://example.com/hero.png'), new LocalizedString(new TranslatedString('en', 'Hero'))),
            enableSmartTap: true,
            redemptionIssuers: ['issuer-1'],
            multipleDevicesAndHoldersAllowedStatus: MultipleDevicesAndHoldersAllowedStatusEnum::MULTIPLE_HOLDERS,
            callbackOptions: new CallbackOptions('https://example.com/callback'),
            securityAnimation: new SecurityAnimation(SecurityAnimationTypeEnum::FOIL_SHIMMER),
            viewUnlockRequirement: ViewUnlockRequirementEnum::UNLOCK_NOT_REQUIRED,
            messages: $messages,
            imageModulesData: $imageModulesData,
            textModulesData: $textModulesData,
            linksModuleData: $linksModuleData,
            homepageUri: new Uri('https://example.com/offers', 'Offers Page'),
            appLinkData: $appLinkData,
        );

        $object = new OfferObject(
            id: 'offer-object-001',
            classId: 'offer-class-001',
            state: StateEnum::ACTIVE,
            barcode: new Barcode(
                type: BarcodeTypeEnum::CODE_128,
                value: 'OFFER20PCT',
                alternateText: 'OFFER20PCT',
                renderEncoding: BarcodeRenderEncodingEnum::UTF_8,
            ),
            hexBackgroundColor: Color::fromHex('#FF6B35'),
            messages: $messages,
            validTimeInterval: new TimeInterval(
                new GoogleDateTime('2025-06-01T00:00:00Z'),
                new GoogleDateTime('2025-12-31T23:59:59Z'),
            ),
            smartTapRedemptionValue: 'offer-redeem-001',
            disableExpirationNotification: false,
            imageModulesData: $imageModulesData,
            textModulesData: $textModulesData,
            linksModuleData: $linksModuleData,
            appLinkData: $appLinkData,
            rotatingBarcode: new RotatingBarcode(
                type: BarcodeTypeEnum::QR_CODE,
                renderEncoding: BarcodeRenderEncodingEnum::UTF_8,
                valuePattern: 'OFFER_{0}',
            ),
            heroImage: new Image(new ImageUri('https://example.com/hero.png')),
            groupingInfo: new GroupingInfo('offer-group', 3),
            passConstraints: new PassConstraints(ScreenshotEligibilityEnum::ELIGIBLE, [NfcConstraintEnum::BLOCK_PAYMENT]),
            linkedObjectIds: ['linked-1', 'linked-2'],
        );

        $classData = $this->serializer->normalize($class);

        self::assertSame('offer-class-001', $classData['id']);
        self::assertSame('Deal Store', $classData['issuerName']);
        self::assertSame('20% Off Everything', $classData['title']);
        self::assertSame('Deal Store Inc.', $classData['provider']);
        self::assertSame('BOTH', $classData['redemptionChannel']);
        self::assertSame('APPROVED', $classData['reviewStatus']);
        self::assertSame('Get 20% off on all items in store and online.', $classData['details']);
        self::assertSame('Cannot be combined with other offers. Valid until Dec 31.', $classData['finePrint']);
        self::assertSame('20% Off', $classData['shortTitle']);
        self::assertSame('#ff6b35', $classData['hexBackgroundColor']);
        self::assertSame('US', $classData['countryCode']);
        self::assertTrue($classData['enableSmartTap']);
        self::assertSame(['issuer-1'], $classData['redemptionIssuers']);
        self::assertSame('MULTIPLE_HOLDERS', $classData['multipleDevicesAndHoldersAllowedStatus']);
        self::assertSame('UNLOCK_NOT_REQUIRED', $classData['viewUnlockRequirement']);

        self::assertArrayHasKey('titleImage', $classData);
        self::assertSame('https://example.com/img.png', $classData['titleImage']['sourceUri']['uri']);

        self::assertArrayHasKey('wideTitleImage', $classData);
        self::assertSame('https://example.com/wide-title.png', $classData['wideTitleImage']['sourceUri']['uri']);

        self::assertArrayHasKey('helpUri', $classData);
        self::assertSame('https://example.com/help', $classData['helpUri']['uri']);
        self::assertSame('Help Center', $classData['helpUri']['description']);

        self::assertArrayHasKey('localizedTitle', $classData);
        self::assertSame('20% Off Everything', $classData['localizedTitle']['defaultValue']['value']);

        self::assertArrayHasKey('localizedProvider', $classData);
        self::assertSame('Deal Store Inc.', $classData['localizedProvider']['defaultValue']['value']);

        self::assertArrayHasKey('localizedDetails', $classData);
        self::assertSame('Get 20% off on all items in store and online.', $classData['localizedDetails']['defaultValue']['value']);

        self::assertArrayHasKey('localizedFinePrint', $classData);
        self::assertSame('Cannot be combined with other offers.', $classData['localizedFinePrint']['defaultValue']['value']);

        self::assertArrayHasKey('localizedShortTitle', $classData);
        self::assertSame('20% Off', $classData['localizedShortTitle']['defaultValue']['value']);

        self::assertArrayHasKey('localizedIssuerName', $classData);
        self::assertSame('Deal Store', $classData['localizedIssuerName']['defaultValue']['value']);

        self::assertArrayHasKey('heroImage', $classData);
        self::assertSame('https://example.com/hero.png', $classData['heroImage']['sourceUri']['uri']);

        self::assertArrayHasKey('callbackOptions', $classData);
        self::assertSame('https://example.com/callback', $classData['callbackOptions']['url']);

        self::assertArrayHasKey('securityAnimation', $classData);
        self::assertSame('FOIL_SHIMMER', $classData['securityAnimation']['animationType']);

        self::assertCount(1, $classData['messages']);
        self::assertCount(1, $classData['imageModulesData']);
        self::assertCount(1, $classData['textModulesData']);
        self::assertArrayHasKey('linksModuleData', $classData);

        self::assertArrayHasKey('homepageUri', $classData);
        self::assertSame('https://example.com/offers', $classData['homepageUri']['uri']);
        self::assertSame('Offers Page', $classData['homepageUri']['description']);

        self::assertArrayHasKey('appLinkData', $classData);
        self::assertSame('com.example.app', $classData['appLinkData']['androidAppLinkInfo']['appTarget']['packageName']);

        $objectData = $this->serializer->normalize($object);

        self::assertSame('offer-object-001', $objectData['id']);
        self::assertSame('offer-class-001', $objectData['classId']);
        self::assertSame('ACTIVE', $objectData['state']);
        self::assertSame('#ff6b35', $objectData['hexBackgroundColor']);
        self::assertSame('offer-redeem-001', $objectData['smartTapRedemptionValue']);
        self::assertFalse($objectData['disableExpirationNotification']);
        self::assertSame(['linked-1', 'linked-2'], $objectData['linkedObjectIds']);

        self::assertArrayHasKey('barcode', $objectData);
        self::assertSame('CODE_128', $objectData['barcode']['type']);
        self::assertSame('OFFER20PCT', $objectData['barcode']['value']);
        self::assertSame('OFFER20PCT', $objectData['barcode']['alternateText']);
        self::assertSame('UTF_8', $objectData['barcode']['renderEncoding']);

        self::assertArrayHasKey('validTimeInterval', $objectData);
        self::assertSame('2025-06-01T00:00:00Z', $objectData['validTimeInterval']['start']['date']);
        self::assertSame('2025-12-31T23:59:59Z', $objectData['validTimeInterval']['end']['date']);

        self::assertCount(1, $objectData['messages']);
        self::assertCount(1, $objectData['imageModulesData']);
        self::assertCount(1, $objectData['textModulesData']);
        self::assertArrayHasKey('linksModuleData', $objectData);
        self::assertArrayHasKey('appLinkData', $objectData);

        self::assertArrayHasKey('rotatingBarcode', $objectData);
        self::assertSame('QR_CODE', $objectData['rotatingBarcode']['type']);
        self::assertSame('UTF_8', $objectData['rotatingBarcode']['renderEncoding']);
        self::assertSame('OFFER_{0}', $objectData['rotatingBarcode']['valuePattern']);

        self::assertArrayHasKey('heroImage', $objectData);
        self::assertSame('https://example.com/hero.png', $objectData['heroImage']['sourceUri']['uri']);

        self::assertArrayHasKey('groupingInfo', $objectData);
        self::assertSame('offer-group', $objectData['groupingInfo']['groupingId']);
        self::assertSame(3, $objectData['groupingInfo']['sortIndex']);

        self::assertArrayHasKey('passConstraints', $objectData);
        self::assertSame('ELIGIBLE', $objectData['passConstraints']['screenshotEligibility']);
        self::assertSame(['BLOCK_PAYMENT'], $objectData['passConstraints']['nfcConstraint']);
    }
}
