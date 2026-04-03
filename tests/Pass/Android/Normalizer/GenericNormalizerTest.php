<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Tests\Pass\Android\Normalizer;

use Jolicode\WalletKit\Pass\Android\Model\Generic\ExpiryNotification;
use Jolicode\WalletKit\Pass\Android\Model\Generic\GenericClass;
use Jolicode\WalletKit\Pass\Android\Model\Generic\GenericObject;
use Jolicode\WalletKit\Pass\Android\Model\Generic\GenericTypeEnum;
use Jolicode\WalletKit\Pass\Android\Model\Generic\Notifications;
use Jolicode\WalletKit\Pass\Android\Model\Generic\UpcomingNotification;
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

final class GenericNormalizerTest extends TestCase
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

    public function testGenericPass(): void
    {
        $localizedString = new LocalizedString(new TranslatedString('en', 'Test Value'));
        $image = new Image(new ImageUri('https://example.com/img.png'), new LocalizedString(new TranslatedString('en', 'Image description')));
        $linksModuleData = new LinksModuleData([new Uri('https://example.com', 'Example', null, 'uri-1')]);
        $textModulesData = [new TextModuleData('Header', 'Body', 'text-1')];
        $imageModulesData = [new ImageModuleData(new Image(new ImageUri('https://example.com/module.png')), 'img-1')];
        $messages = [new Message('Title', 'Body', 'msg-1', MessageTypeEnum::Text)];
        $securityAnimation = new SecurityAnimation(SecurityAnimationTypeEnum::FoilShimmer);
        $callbackOptions = new CallbackOptions('https://example.com/callback');
        $appLinkData = new AppLinkData(
            androidAppLinkInfo: new AppLinkInfo(
                title: new LocalizedString(new TranslatedString('en', 'Open App')),
                appTarget: new AppTarget(new Uri('https://example.com/app'), 'com.example.app'),
            ),
        );

        $class = new GenericClass(
            id: 'generic-class-001',
            imageModulesData: $imageModulesData,
            textModulesData: $textModulesData,
            linksModuleData: $linksModuleData,
            enableSmartTap: true,
            redemptionIssuers: ['issuer-1', 'issuer-2'],
            securityAnimation: $securityAnimation,
            multipleDevicesAndHoldersAllowedStatus: MultipleDevicesAndHoldersAllowedStatusEnum::MultipleHolders,
            callbackOptions: $callbackOptions,
            viewUnlockRequirement: ViewUnlockRequirementEnum::UnlockNotRequired,
            messages: $messages,
            appLinkData: $appLinkData,
        );

        $object = new GenericObject(
            id: 'generic-object-001',
            classId: 'generic-class-001',
            genericType: GenericTypeEnum::GymMembership,
            cardTitle: new LocalizedString(new TranslatedString('en', 'My Gym Card')),
            subheader: new LocalizedString(new TranslatedString('en', 'Member')),
            header: new LocalizedString(new TranslatedString('en', 'John Doe')),
            logo: $image,
            wideLogo: new Image(new ImageUri('https://example.com/wide-logo.png')),
            hexBackgroundColor: '#4285F4',
            notifications: new Notifications(
                expiryNotification: new ExpiryNotification(true),
                upcomingNotification: new UpcomingNotification(true),
            ),
            barcode: new Barcode(
                type: BarcodeTypeEnum::QrCode,
                value: 'BARCODE123',
                alternateText: 'ALT',
                renderEncoding: BarcodeRenderEncodingEnum::Utf8,
            ),
            heroImage: new Image(new ImageUri('https://example.com/hero.png'), new LocalizedString(new TranslatedString('en', 'Hero'))),
            validTimeInterval: new TimeInterval(
                new GoogleDateTime('2025-01-01T00:00:00Z'),
                new GoogleDateTime('2025-12-31T23:59:59Z'),
            ),
            imageModulesData: $imageModulesData,
            textModulesData: $textModulesData,
            linksModuleData: $linksModuleData,
            appLinkData: $appLinkData,
            groupingInfo: new GroupingInfo('group-1', 1),
            smartTapRedemptionValue: 'smart-tap-123',
            rotatingBarcode: new RotatingBarcode(
                type: BarcodeTypeEnum::QrCode,
                renderEncoding: BarcodeRenderEncodingEnum::Utf8,
                valuePattern: 'PATTERN_{0}',
            ),
            state: StateEnum::Active,
            messages: $messages,
            passConstraints: new PassConstraints(ScreenshotEligibilityEnum::Eligible, [NfcConstraintEnum::BlockPayment]),
            linkedObjectIds: ['linked-obj-1', 'linked-obj-2'],
        );

        $classData = $this->serializer->normalize($class);

        self::assertSame('generic-class-001', $classData['id']);
        self::assertTrue($classData['enableSmartTap']);
        self::assertSame(['issuer-1', 'issuer-2'], $classData['redemptionIssuers']);
        self::assertSame('MULTIPLE_HOLDERS', $classData['multipleDevicesAndHoldersAllowedStatus']);
        self::assertSame('UNLOCK_NOT_REQUIRED', $classData['viewUnlockRequirement']);

        self::assertArrayHasKey('securityAnimation', $classData);
        self::assertSame('FOIL_SHIMMER', $classData['securityAnimation']['animationType']);

        self::assertArrayHasKey('callbackOptions', $classData);
        self::assertSame('https://example.com/callback', $classData['callbackOptions']['url']);

        self::assertCount(1, $classData['textModulesData']);
        self::assertSame('Header', $classData['textModulesData'][0]['header']);
        self::assertSame('Body', $classData['textModulesData'][0]['body']);
        self::assertSame('text-1', $classData['textModulesData'][0]['id']);

        self::assertCount(1, $classData['imageModulesData']);
        self::assertSame('img-1', $classData['imageModulesData'][0]['id']);
        self::assertArrayHasKey('mainImage', $classData['imageModulesData'][0]);

        self::assertArrayHasKey('linksModuleData', $classData);
        self::assertCount(1, $classData['linksModuleData']['uris']);
        self::assertSame('https://example.com', $classData['linksModuleData']['uris'][0]['uri']);
        self::assertSame('Example', $classData['linksModuleData']['uris'][0]['description']);

        self::assertCount(1, $classData['messages']);
        self::assertSame('Title', $classData['messages'][0]['header']);
        self::assertSame('TEXT', $classData['messages'][0]['messageType']);

        self::assertArrayHasKey('appLinkData', $classData);
        self::assertArrayHasKey('androidAppLinkInfo', $classData['appLinkData']);
        self::assertSame('com.example.app', $classData['appLinkData']['androidAppLinkInfo']['appTarget']['packageName']);

        $objectData = $this->serializer->normalize($object);

        self::assertSame('generic-object-001', $objectData['id']);
        self::assertSame('generic-class-001', $objectData['classId']);
        self::assertSame('GENERIC_GYM_MEMBERSHIP', $objectData['genericType']);
        self::assertSame('#4285F4', $objectData['hexBackgroundColor']);
        self::assertSame('ACTIVE', $objectData['state']);
        self::assertSame('smart-tap-123', $objectData['smartTapRedemptionValue']);
        self::assertSame(['linked-obj-1', 'linked-obj-2'], $objectData['linkedObjectIds']);

        self::assertArrayHasKey('cardTitle', $objectData);
        self::assertSame('My Gym Card', $objectData['cardTitle']['defaultValue']['value']);

        self::assertArrayHasKey('subheader', $objectData);
        self::assertSame('Member', $objectData['subheader']['defaultValue']['value']);

        self::assertArrayHasKey('header', $objectData);
        self::assertSame('John Doe', $objectData['header']['defaultValue']['value']);

        self::assertArrayHasKey('logo', $objectData);
        self::assertSame('https://example.com/img.png', $objectData['logo']['sourceUri']['uri']);
        self::assertSame('Image description', $objectData['logo']['contentDescription']['defaultValue']['value']);

        self::assertArrayHasKey('wideLogo', $objectData);
        self::assertSame('https://example.com/wide-logo.png', $objectData['wideLogo']['sourceUri']['uri']);

        self::assertArrayHasKey('notifications', $objectData);
        self::assertTrue($objectData['notifications']['expiryNotification']['enableNotification']);
        self::assertTrue($objectData['notifications']['upcomingNotification']['enableNotification']);

        self::assertArrayHasKey('barcode', $objectData);
        self::assertSame('QR_CODE', $objectData['barcode']['type']);
        self::assertSame('BARCODE123', $objectData['barcode']['value']);
        self::assertSame('ALT', $objectData['barcode']['alternateText']);
        self::assertSame('UTF_8', $objectData['barcode']['renderEncoding']);

        self::assertArrayHasKey('heroImage', $objectData);
        self::assertSame('https://example.com/hero.png', $objectData['heroImage']['sourceUri']['uri']);

        self::assertArrayHasKey('validTimeInterval', $objectData);
        self::assertSame('2025-01-01T00:00:00Z', $objectData['validTimeInterval']['start']['date']);
        self::assertSame('2025-12-31T23:59:59Z', $objectData['validTimeInterval']['end']['date']);

        self::assertCount(1, $objectData['imageModulesData']);
        self::assertCount(1, $objectData['textModulesData']);
        self::assertArrayHasKey('linksModuleData', $objectData);
        self::assertArrayHasKey('appLinkData', $objectData);

        self::assertArrayHasKey('groupingInfo', $objectData);
        self::assertSame('group-1', $objectData['groupingInfo']['groupingId']);
        self::assertSame(1, $objectData['groupingInfo']['sortIndex']);

        self::assertArrayHasKey('rotatingBarcode', $objectData);
        self::assertSame('QR_CODE', $objectData['rotatingBarcode']['type']);
        self::assertSame('UTF_8', $objectData['rotatingBarcode']['renderEncoding']);
        self::assertSame('PATTERN_{0}', $objectData['rotatingBarcode']['valuePattern']);

        self::assertCount(1, $objectData['messages']);

        self::assertArrayHasKey('passConstraints', $objectData);
        self::assertSame('ELIGIBLE', $objectData['passConstraints']['screenshotEligibility']);
        self::assertSame(['BLOCK_PAYMENT'], $objectData['passConstraints']['nfcConstraint']);
    }
}
