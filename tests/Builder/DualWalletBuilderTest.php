<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Tests\Builder;

use Jolicode\WalletKit\Builder\GoogleVerticalEnum;
use Jolicode\WalletKit\Builder\GoogleWalletContext;
use Jolicode\WalletKit\Builder\WalletPass;
use Jolicode\WalletKit\Builder\WalletPlatformContext;
use Jolicode\WalletKit\Exception\ApplePassNotAvailableException;
use Jolicode\WalletKit\Exception\GoogleWalletPairNotAvailableException;
use Jolicode\WalletKit\Exception\InvalidWalletPlatformContextException;
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
        return WalletPlatformContext::both(
            appleTeamIdentifier: 'TEAM1',
            applePassTypeIdentifier: 'pass.com.example.test',
            appleSerialNumber: 'SN-001',
            appleOrganizationName: 'Example Org',
            appleDescription: 'Test pass',
            googleClassId: '3388000000012345.test_class',
            googleObjectId: '3388000000012345.test_object',
            defaultGoogleReviewStatus: ReviewStatusEnum::APPROVED,
            defaultGoogleObjectState: StateEnum::ACTIVE,
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

    public function testEmptyPlatformContextThrows(): void
    {
        $this->expectException(InvalidWalletPlatformContextException::class);
        new WalletPlatformContext(null, null);
    }

    public function testGoogleOnlyWithoutIssuerThrows(): void
    {
        $this->expectException(InvalidWalletPlatformContextException::class);
        new WalletPlatformContext(null, new GoogleWalletContext(
            classId: 'c',
            objectId: 'o',
            issuerName: null,
        ));
    }

    public function testGoogleOnlyBuildAppleAccessorThrows(): void
    {
        $ctx = WalletPlatformContext::googleOnly(
            googleClassId: '3388000000012345.g_only_class',
            googleObjectId: '3388000000012345.g_only_object',
            issuerName: 'Issuer Inc.',
            defaultGoogleReviewStatus: ReviewStatusEnum::APPROVED,
            defaultGoogleObjectState: StateEnum::ACTIVE,
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
        $ctx = WalletPlatformContext::googleOnly(
            googleClassId: '3388000000012345.g_only_class',
            googleObjectId: '3388000000012345.g_only_object',
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
        $ctx = WalletPlatformContext::appleOnly(
            appleTeamIdentifier: 'TEAM1',
            applePassTypeIdentifier: 'pass.com.example.test',
            appleSerialNumber: 'SN-A',
            appleOrganizationName: 'Example Org',
            appleDescription: 'Apple only',
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
}
