<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Tests\Builder;

use Jolicode\WalletKit\Builder\GoogleVerticalEnum;
use Jolicode\WalletKit\Builder\WalletPass;
use Jolicode\WalletKit\Builder\WalletPlatformContext;
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
        return new WalletPlatformContext(
            appleTeamIdentifier: 'TEAM1',
            applePassTypeIdentifier: 'pass.com.example.test',
            appleSerialNumber: 'SN-001',
            appleOrganizationName: 'Example Org',
            appleDescription: 'Test pass',
            googleClassId: '3388000000012345.test_class',
            googleObjectId: '3388000000012345.test_object',
            defaultGoogleReviewStatus: ReviewStatusEnum::Approved,
            defaultGoogleObjectState: StateEnum::Active,
        );
    }

    public function testGenericBuildNormalizes(): void
    {
        $built = WalletPass::generic($this->context())
            ->withPassStructure(new PassStructure(
                primaryFields: [new Field(key: 'info', value: 'Hello', label: 'Info')],
            ))
            ->withGenericType(GenericTypeEnum::Unspecified)
            ->withGoogleCardTitle('Card')
            ->addAppleBarcode(new Barcode(altText: 'x', format: BarcodeFormatEnum::QR, message: 'M1', messageEncoding: 'utf-8'))
            ->withGoogleHexBackgroundColor('#112233')
            ->withGrouping('grp', 1)
            ->build();

        self::assertSame(PassTypeEnum::Generic, $built->apple()->passType);
        self::assertSame(GoogleVerticalEnum::Generic, $built->googleVertical());

        $appleJson = $this->serializer->normalize($built->apple());
        self::assertArrayHasKey('generic', $appleJson);
        self::assertSame('SN-001', $appleJson['serialNumber']);

        $pair = $built->google();
        $classJson = $this->serializer->normalize($pair->issuerClass);
        $objectJson = $this->serializer->normalize($pair->passObject);
        self::assertSame($this->context()->googleClassId, $classJson['id']);
        self::assertSame($this->context()->googleClassId, $objectJson['classId']);
        self::assertSame('QR_CODE', $objectJson['barcode']['type']);
    }

    public function testOfferBuildNormalizes(): void
    {
        $built = WalletPass::offer(
            $this->context(),
            'Summer sale',
            'Example Provider',
            RedemptionChannelEnum::Both,
        )
            ->withBackgroundColorRgb('rgb(10, 20, 30)')
            ->build();

        self::assertSame(PassTypeEnum::Coupon, $built->apple()->passType);
        self::assertSame(GoogleVerticalEnum::Offer, $built->googleVertical());
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

        self::assertSame(PassTypeEnum::StoreCard, $built->apple()->passType);
        self::assertSame(GoogleVerticalEnum::Loyalty, $built->googleVertical());
        $appleJson = $this->serializer->normalize($built->apple());
        self::assertArrayHasKey('storeCard', $appleJson);
    }

    public function testEventTicketBuildNormalizes(): void
    {
        $built = WalletPass::eventTicket($this->context(), 'Big Concert')
            ->withTicketHolderName('Alex')
            ->withTicketNumber('TIX-42')
            ->build();

        self::assertSame(PassTypeEnum::EventTicket, $built->apple()->passType);
        self::assertSame(GoogleVerticalEnum::EventTicket, $built->googleVertical());
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

        self::assertSame(PassTypeEnum::BoardingPass, $built->apple()->passType);
        self::assertSame(GoogleVerticalEnum::Flight, $built->googleVertical());
        $appleJson = $this->serializer->normalize($built->apple());
        self::assertArrayHasKey('boardingPass', $appleJson);
        self::assertSame('PKTransitTypeAir', $appleJson['boardingPass']['transitType']);
    }

    public function testTransitBuildNormalizes(): void
    {
        $built = WalletPass::transit(
            $this->context(),
            TransitTypeEnum::Bus,
            TripTypeEnum::OneWay,
        )
            ->withTicketNumber('BUS-7')
            ->build();

        self::assertSame(PassTypeEnum::BoardingPass, $built->apple()->passType);
        self::assertSame(GoogleVerticalEnum::Transit, $built->googleVertical());
        $appleJson = $this->serializer->normalize($built->apple());
        self::assertSame('PKTransitTypeBus', $appleJson['boardingPass']['transitType']);
    }

    public function testGiftCardBuildNormalizes(): void
    {
        $built = WalletPass::giftCard($this->context(), '4111111111111111')
            ->withPin('1234')
            ->build();

        self::assertSame(PassTypeEnum::StoreCard, $built->apple()->passType);
        self::assertSame(GoogleVerticalEnum::GiftCard, $built->googleVertical());
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
            RedemptionChannelEnum::Online,
        )
            ->addAppleBarcode(new Barcode(null, BarcodeFormatEnum::QR, 'first', 'utf-8'))
            ->addAppleBarcode(new Barcode(null, BarcodeFormatEnum::CODE128, 'second', 'utf-8'))
            ->build();

        $objectJson = $this->serializer->normalize($built->google()->passObject);
        self::assertSame('first', $objectJson['barcode']['value']);
    }
}
