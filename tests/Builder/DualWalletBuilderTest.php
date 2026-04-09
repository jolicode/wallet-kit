<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Tests\Builder;

use Jolicode\WalletKit\Builder\GoogleVerticalEnum;
use Jolicode\WalletKit\Builder\WalletPass;
use Jolicode\WalletKit\Builder\WalletPlatformContext;
use Jolicode\WalletKit\Exception\ApplePassNotAvailableException;
use Jolicode\WalletKit\Exception\GoogleWalletPairNotAvailableException;
use Jolicode\WalletKit\Exception\SamsungCardNotAvailableException;
use Jolicode\WalletKit\Pass\Android\Model\Flight\AirportInfo;
use Jolicode\WalletKit\Pass\Android\Model\Flight\FlightCarrier;
use Jolicode\WalletKit\Pass\Android\Model\Flight\FlightHeader;
use Jolicode\WalletKit\Pass\Android\Model\Flight\ReservationInfo;
use Jolicode\WalletKit\Pass\Android\Model\Generic\GenericTypeEnum;
use Jolicode\WalletKit\Pass\Android\Model\Offer\RedemptionChannelEnum;
use Jolicode\WalletKit\Pass\Android\Model\Shared\ReviewStatusEnum;
use Jolicode\WalletKit\Pass\Android\Model\Shared\StateEnum;
use Jolicode\WalletKit\Pass\Android\Model\Transit\TransitTypeEnum;
use Jolicode\WalletKit\Pass\Android\Model\Transit\TripTypeEnum;
use Jolicode\WalletKit\Pass\Apple\Model\Barcode;
use Jolicode\WalletKit\Pass\Apple\Model\BarcodeFormatEnum;
use Jolicode\WalletKit\Pass\Apple\Model\Field;
use Jolicode\WalletKit\Pass\Apple\Model\PassStructure;
use Jolicode\WalletKit\Pass\Apple\Model\PassTypeEnum;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Serializer;

final class DualWalletBuilderTest extends TestCase
{
    private Serializer $serializer;

    protected function setUp(): void
    {
        $this->serializer = BuilderTestSerializerFactory::create();
    }

    private function context(): WalletPlatformContext
    {
        return (new WalletPlatformContext())
            ->withApple(
                teamIdentifier: 'TEAM1',
                passTypeIdentifier: 'pass.com.example.test',
                serialNumber: 'SN-001',
                organizationName: 'Example Org',
                description: 'Test pass',
            )
            ->withGoogle(
                classId: '3388000000012345.test_class',
                objectId: '3388000000012345.test_object',
                defaultReviewStatus: ReviewStatusEnum::APPROVED,
                defaultObjectState: StateEnum::ACTIVE,
            );
    }

    public function testGenericBuildNormalizes(): void
    {
        $ctx = $this->context();
        $built = WalletPass::generic($ctx)
            ->withPassStructure(new PassStructure(
                primaryFields: [new Field(key: 'info', value: 'Hello', label: 'Info')],
            ))
            ->withGenericType(GenericTypeEnum::UNSPECIFIED)
            ->withGoogleCardTitle('Card')
            ->addAppleBarcode(new Barcode(altText: 'x', format: BarcodeFormatEnum::QR, message: 'M1', messageEncoding: 'utf-8'))
            ->withGoogleHexBackgroundColor('#112233')
            ->withGrouping('grp', 1)
            ->build();

        self::assertSame(PassTypeEnum::GENERIC, $built->apple()->passType);
        self::assertSame(GoogleVerticalEnum::GENERIC, $built->googleVertical());

        $appleJson = $this->serializer->normalize($built->apple());
        self::assertArrayHasKey('generic', $appleJson);
        self::assertSame('SN-001', $appleJson['serialNumber']);

        $pair = $built->google();
        $classJson = $this->serializer->normalize($pair->issuerClass);
        $objectJson = $this->serializer->normalize($pair->passObject);
        self::assertSame($ctx->google->classId, $classJson['id']);
        self::assertSame($ctx->google->classId, $objectJson['classId']);
        self::assertSame('QR_CODE', $objectJson['barcode']['type']);
    }

    public function testOfferBuildNormalizes(): void
    {
        $built = WalletPass::offer(
            $this->context(),
            'Summer sale',
            'Example Provider',
            RedemptionChannelEnum::BOTH,
        )
            ->withBackgroundColorRgb('rgb(10, 20, 30)')
            ->build();

        self::assertSame(PassTypeEnum::COUPON, $built->apple()->passType);
        self::assertSame(GoogleVerticalEnum::OFFER, $built->googleVertical());
        $appleJson = $this->serializer->normalize($built->apple());
        self::assertArrayHasKey('coupon', $appleJson);

        $objectJson = $this->serializer->normalize($built->google()->passObject);
        self::assertSame('ACTIVE', $objectJson['state']);
    }

    public function testLoyaltyBuildNormalizes(): void
    {
        $built = WalletPass::loyalty($this->context(), 'Rewards')
            ->withAccount('Jane Doe', 'ACC-9')
            ->build();

        self::assertSame(PassTypeEnum::STORE_CARD, $built->apple()->passType);
        self::assertSame(GoogleVerticalEnum::LOYALTY, $built->googleVertical());
        $appleJson = $this->serializer->normalize($built->apple());
        self::assertArrayHasKey('storeCard', $appleJson);
    }

    public function testEventTicketBuildNormalizes(): void
    {
        $built = WalletPass::eventTicket($this->context(), 'Big Concert')
            ->withTicketHolderName('Alex')
            ->withTicketNumber('TIX-42')
            ->build();

        self::assertSame(PassTypeEnum::EVENT_TICKET, $built->apple()->passType);
        self::assertSame(GoogleVerticalEnum::EVENT_TICKET, $built->googleVertical());
        $objectJson = $this->serializer->normalize($built->google()->passObject);
        self::assertSame('TIX-42', $objectJson['ticketNumber']);
    }

    public function testFlightBuildNormalizes(): void
    {
        $header = new FlightHeader(
            carrier: new FlightCarrier(carrierIataCode: 'ZZ'),
            flightNumber: '101',
        );
        $built = WalletPass::flight(
            $this->context(),
            'Pat Lee',
            new ReservationInfo(confirmationCode: 'ABC'),
            $header,
            new AirportInfo(airportIataCode: 'SFO'),
            new AirportInfo(airportIataCode: 'LAX'),
        )->build();

        self::assertSame(PassTypeEnum::BOARDING_PASS, $built->apple()->passType);
        self::assertSame(GoogleVerticalEnum::FLIGHT, $built->googleVertical());
        $appleJson = $this->serializer->normalize($built->apple());
        self::assertArrayHasKey('boardingPass', $appleJson);
        self::assertSame('PKTransitTypeAir', $appleJson['boardingPass']['transitType']);
    }

    public function testTransitBuildNormalizes(): void
    {
        $built = WalletPass::transit(
            $this->context(),
            TransitTypeEnum::BUS,
            TripTypeEnum::ONE_WAY,
        )
            ->withTicketNumber('BUS-7')
            ->build();

        self::assertSame(PassTypeEnum::BOARDING_PASS, $built->apple()->passType);
        self::assertSame(GoogleVerticalEnum::TRANSIT, $built->googleVertical());
        $appleJson = $this->serializer->normalize($built->apple());
        self::assertSame('PKTransitTypeBus', $appleJson['boardingPass']['transitType']);
    }

    public function testGiftCardBuildNormalizes(): void
    {
        $built = WalletPass::giftCard($this->context(), '4111111111111111')
            ->withPin('1234')
            ->build();

        self::assertSame(PassTypeEnum::STORE_CARD, $built->apple()->passType);
        self::assertSame(GoogleVerticalEnum::GIFT_CARD, $built->googleVertical());
        $appleJson = $this->serializer->normalize($built->apple());
        self::assertArrayHasKey('storeCard', $appleJson);
        $objectJson = $this->serializer->normalize($built->google()->passObject);
        self::assertSame('4111111111111111', $objectJson['cardNumber']);
    }

    public function testMutateApple(): void
    {
        $built = WalletPass::generic($this->context())
            ->mutateApple(static function ($pass): void {
                $pass->logoText = 'Logo';
            })
            ->build();

        self::assertSame('Logo', $built->apple()->logoText);
    }

    public function testGoogleBarcodeUsesFirstAppleBarcodeWhenMultiple(): void
    {
        $built = WalletPass::offer(
            $this->context(),
            'T',
            'P',
            RedemptionChannelEnum::ONLINE,
        )
            ->addAppleBarcode(new Barcode(null, BarcodeFormatEnum::QR, 'first', 'utf-8'))
            ->addAppleBarcode(new Barcode(null, BarcodeFormatEnum::CODE_128, 'second', 'utf-8'))
            ->build();

        $objectJson = $this->serializer->normalize($built->google()->passObject);
        self::assertSame('first', $objectJson['barcode']['value']);
    }

    public function testGoogleOnlyBuildAppleAccessorThrows(): void
    {
        $ctx = (new WalletPlatformContext())->withGoogle(
            classId: '3388000000012345.g_only_class',
            objectId: '3388000000012345.g_only_object',
            issuerName: 'Issuer Inc.',
            defaultReviewStatus: ReviewStatusEnum::APPROVED,
            defaultObjectState: StateEnum::ACTIVE,
        );

        $built = WalletPass::generic($ctx)
            ->withPassStructure(new PassStructure(
                primaryFields: [new Field(key: 'info', value: 'Hello', label: 'Info')],
            ))
            ->withGenericType(GenericTypeEnum::UNSPECIFIED)
            ->withGoogleCardTitle('Card')
            ->addAppleBarcode(new Barcode(altText: 'x', format: BarcodeFormatEnum::QR, message: 'M1', messageEncoding: 'utf-8'))
            ->build();

        $this->expectException(ApplePassNotAvailableException::class);
        $built->apple();
    }

    public function testGoogleOnlyBuildGoogleNormalizes(): void
    {
        $ctx = (new WalletPlatformContext())->withGoogle(
            classId: '3388000000012345.g_only_class',
            objectId: '3388000000012345.g_only_object',
            issuerName: 'Issuer Inc.',
        );

        $built = WalletPass::offer(
            $ctx,
            'Deal',
            'Shop',
            RedemptionChannelEnum::INSTORE,
        )->build();

        self::assertSame(GoogleVerticalEnum::OFFER, $built->googleVertical());
        $classJson = $this->serializer->normalize($built->google()->issuerClass);
        self::assertSame('Issuer Inc.', $classJson['issuerName']);
    }

    public function testAppleOnlyBuildGoogleAccessorThrows(): void
    {
        $ctx = (new WalletPlatformContext())->withApple(
            teamIdentifier: 'TEAM1',
            passTypeIdentifier: 'pass.com.example.test',
            serialNumber: 'SN-A',
            organizationName: 'Example Org',
            description: 'Apple only',
        );

        $built = WalletPass::generic($ctx)
            ->withPassStructure(new PassStructure(
                primaryFields: [new Field(key: 'k', value: 'v', label: 'L')],
            ))
            ->withGenericType(GenericTypeEnum::UNSPECIFIED)
            ->build();

        self::assertSame('SN-A', $built->apple()->serialNumber);

        $this->expectException(GoogleWalletPairNotAvailableException::class);
        $built->google();
    }

    public function testSamsungOnlyContext(): void
    {
        $ctx = (new WalletPlatformContext())->withSamsung(
            refId: 'ref-001',
            appLinkLogo: 'https://example.com/logo.png',
            appLinkName: 'App',
            appLinkData: 'https://example.com',
        );

        self::assertTrue($ctx->hasSamsung());
        self::assertFalse($ctx->hasApple());
        self::assertFalse($ctx->hasGoogle());
    }

    public function testSamsungOnlyBuildOfferNormalizes(): void
    {
        $ctx = (new WalletPlatformContext())->withSamsung(
            refId: 'ref-samsung-offer',
            appLinkLogo: 'https://example.com/logo.png',
            appLinkName: 'Shop',
            appLinkData: 'https://example.com',
        );

        $built = WalletPass::offer($ctx, 'Summer sale', 'Example Provider', RedemptionChannelEnum::BOTH)->build();

        $card = $built->samsung();
        self::assertSame('coupon', $card->type->value);
        self::assertSame('others', $card->subType->value);
        self::assertCount(1, $card->data);
        self::assertSame('ref-samsung-offer', $card->data[0]->refId);

        $cardJson = $this->serializer->normalize($card);
        self::assertSame('coupon', $cardJson['card']['type']);
        self::assertSame('Summer sale', $cardJson['card']['data'][0]['attributes']['title']);
    }

    public function testSamsungOnlyBuildAppleAccessorThrows(): void
    {
        $ctx = (new WalletPlatformContext())->withSamsung(refId: 'ref-001');

        $built = WalletPass::generic($ctx)->build();

        $this->expectException(ApplePassNotAvailableException::class);
        $built->apple();
    }

    public function testSamsungOnlyBuildGoogleAccessorThrows(): void
    {
        $ctx = (new WalletPlatformContext())->withSamsung(refId: 'ref-001');

        $built = WalletPass::generic($ctx)->build();

        $this->expectException(GoogleWalletPairNotAvailableException::class);
        $built->google();
    }

    public function testDualWithoutSamsungThrowsOnSamsungAccessor(): void
    {
        $built = WalletPass::generic($this->context())->build();

        $this->expectException(SamsungCardNotAvailableException::class);
        $built->samsung();
    }

    public function testAllPlatformsContext(): void
    {
        $ctx = (new WalletPlatformContext())
            ->withApple(
                teamIdentifier: 'TEAM1',
                passTypeIdentifier: 'pass.com.example.test',
                serialNumber: 'SN-ALL',
                organizationName: 'Example Org',
                description: 'All platforms',
            )
            ->withGoogle(
                classId: '3388000000012345.all_class',
                objectId: '3388000000012345.all_object',
            )
            ->withSamsung(
                refId: 'ref-all',
                appLinkLogo: 'https://example.com/logo.png',
                appLinkName: 'App',
                appLinkData: 'https://example.com',
            );

        $built = WalletPass::offer($ctx, 'Deal', 'Shop', RedemptionChannelEnum::INSTORE)->build();

        self::assertSame(PassTypeEnum::COUPON, $built->apple()->passType);
        self::assertSame(GoogleVerticalEnum::OFFER, $built->googleVertical());
        self::assertSame('coupon', $built->samsung()->type->value);

        $samsungJson = $this->serializer->normalize($built->samsung());
        self::assertSame('Deal', $samsungJson['card']['data'][0]['attributes']['title']);
    }

    public function testMutateSamsung(): void
    {
        $ctx = (new WalletPlatformContext())->withSamsung(refId: 'ref-mut');

        $built = WalletPass::generic($ctx)
            ->mutateSamsung(static function ($card): void {
                $card->data[0]->refId = 'ref-mutated';
            })
            ->build();

        self::assertSame('ref-mutated', $built->samsung()->data[0]->refId);
    }

    public function testSamsungFlightBoardingPass(): void
    {
        $ctx = (new WalletPlatformContext())->withSamsung(
            refId: 'ref-flight',
            appLinkLogo: 'https://example.com/logo.png',
            appLinkName: 'Airlines',
            appLinkData: 'https://example.com',
        );

        $built = WalletPass::flight(
            $ctx,
            'Pat Lee',
            new ReservationInfo(confirmationCode: 'ABC'),
            new FlightHeader(carrier: new FlightCarrier(carrierIataCode: 'ZZ'), flightNumber: '101'),
            new AirportInfo(airportIataCode: 'SFO'),
            new AirportInfo(airportIataCode: 'LAX'),
        )->build();

        $card = $built->samsung();
        self::assertSame('boardingpass', $card->type->value);
        self::assertSame('airlines', $card->subType->value);

        $cardJson = $this->serializer->normalize($card);
        $attrs = $cardJson['card']['data'][0]['attributes'];
        self::assertSame('Pat Lee', $attrs['user']);
        self::assertSame('SFO', $attrs['departCode']);
        self::assertSame('LAX', $attrs['arriveCode']);
    }

    public function testSamsungTransitBus(): void
    {
        $ctx = (new WalletPlatformContext())->withSamsung(
            refId: 'ref-bus',
            appLinkLogo: 'https://example.com/logo.png',
            appLinkName: 'Transit',
            appLinkData: 'https://example.com',
        );

        $built = WalletPass::transit($ctx, TransitTypeEnum::BUS, TripTypeEnum::ONE_WAY)
            ->withTicketNumber('BUS-7')
            ->build();

        $card = $built->samsung();
        self::assertSame('boardingpass', $card->type->value);
        self::assertSame('buses', $card->subType->value);
    }

    public function testWithMethodsAreImmutable(): void
    {
        $base = new WalletPlatformContext();
        $withApple = $base->withApple(
            teamIdentifier: 'TEAM1',
            passTypeIdentifier: 'pass.com.example.test',
            serialNumber: 'SN-1',
            organizationName: 'Org',
            description: 'Desc',
        );

        self::assertFalse($base->hasApple());
        self::assertTrue($withApple->hasApple());
        self::assertNotSame($base, $withApple);
    }
}
