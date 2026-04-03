<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Tests\Pass\Apple\Normalizer;

use Jolicode\WalletKit\Pass\Apple\Model\Barcode;
use Jolicode\WalletKit\Pass\Apple\Model\BarcodeFormatEnum;
use Jolicode\WalletKit\Pass\Apple\Model\Beacon;
use Jolicode\WalletKit\Pass\Apple\Model\DateStyleEnum;
use Jolicode\WalletKit\Pass\Apple\Model\EventTypeEnum;
use Jolicode\WalletKit\Pass\Apple\Model\Field;
use Jolicode\WalletKit\Pass\Apple\Model\Location;
use Jolicode\WalletKit\Pass\Apple\Model\Nfc;
use Jolicode\WalletKit\Pass\Apple\Model\NumberStyleEnum;
use Jolicode\WalletKit\Pass\Apple\Model\Pass;
use Jolicode\WalletKit\Pass\Apple\Model\PassStructure;
use Jolicode\WalletKit\Pass\Apple\Model\PassTypeEnum;
use Jolicode\WalletKit\Pass\Apple\Model\RelevantDate;
use Jolicode\WalletKit\Pass\Apple\Model\SemanticTags;
use Jolicode\WalletKit\Pass\Apple\Model\SemanticTagType\CurrencyAmount;
use Jolicode\WalletKit\Pass\Apple\Model\SemanticTagType\EventDateInfo;
use Jolicode\WalletKit\Pass\Apple\Model\SemanticTagType\PersonNameComponents;
use Jolicode\WalletKit\Pass\Apple\Model\SemanticTagType\Seat;
use Jolicode\WalletKit\Pass\Apple\Model\SemanticTagType\SemanticLocation;
use Jolicode\WalletKit\Pass\Apple\Model\SemanticTagType\WifiNetwork;
use Jolicode\WalletKit\Pass\Apple\Model\TextAlignmentEnum;
use Jolicode\WalletKit\Pass\Apple\Model\TransitTypeEnum;
use Jolicode\WalletKit\Pass\Apple\Normalizer\BarcodeNormalizer;
use Jolicode\WalletKit\Pass\Apple\Normalizer\BeaconNormalizer;
use Jolicode\WalletKit\Pass\Apple\Normalizer\FieldNormalizer;
use Jolicode\WalletKit\Pass\Apple\Normalizer\LocationNormalizer;
use Jolicode\WalletKit\Pass\Apple\Normalizer\NfcNormalizer;
use Jolicode\WalletKit\Pass\Apple\Normalizer\PassNormalizer;
use Jolicode\WalletKit\Pass\Apple\Normalizer\PassStructureNormalizer;
use Jolicode\WalletKit\Pass\Apple\Normalizer\RelevantDateNormalizer;
use Jolicode\WalletKit\Pass\Apple\Normalizer\SemanticTagsNormalizer;
use Jolicode\WalletKit\Pass\Apple\Normalizer\SemanticTagType\CurrencyAmountNormalizer;
use Jolicode\WalletKit\Pass\Apple\Normalizer\SemanticTagType\EventDateInfoNormalizer;
use Jolicode\WalletKit\Pass\Apple\Normalizer\SemanticTagType\PersonNameComponentsNormalizer;
use Jolicode\WalletKit\Pass\Apple\Normalizer\SemanticTagType\SeatNormalizer;
use Jolicode\WalletKit\Pass\Apple\Normalizer\SemanticTagType\SemanticLocationNormalizer;
use Jolicode\WalletKit\Pass\Apple\Normalizer\SemanticTagType\WifiNetworkNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Serializer;

final class PassNormalizerTest extends TestCase
{
    private Serializer $serializer;

    protected function setUp(): void
    {
        $this->serializer = new Serializer([
            new PassNormalizer(),
            new PassStructureNormalizer(),
            new FieldNormalizer(),
            new BarcodeNormalizer(),
            new NfcNormalizer(),
            new LocationNormalizer(),
            new BeaconNormalizer(),
            new RelevantDateNormalizer(),
            new SemanticTagsNormalizer(),
            new SeatNormalizer(),
            new PersonNameComponentsNormalizer(),
            new CurrencyAmountNormalizer(),
            new SemanticLocationNormalizer(),
            new EventDateInfoNormalizer(),
            new WifiNetworkNormalizer(),
        ]);
    }

    public function testBoardingPass(): void
    {
        $pass = new Pass(
            description: 'Boarding pass for SFO to LHR',
            organizationName: 'Example Airlines',
            teamIdentifier: 'A1B2C3D4E5',
            passTypeIdentifier: 'pass.com.example.boarding',
            formatVersion: 1,
            serialNumber: 'BP-001',
            passType: PassTypeEnum::BoardingPass,
            structure: new PassStructure(
                headerFields: [
                    new Field(key: 'gate', value: 'F12', label: 'Gate'),
                ],
                primaryFields: [
                    new Field(key: 'origin', value: 'SFO', label: 'San Francisco'),
                    new Field(key: 'destination', value: 'LHR', label: 'London'),
                ],
                secondaryFields: [
                    new Field(
                        key: 'boarding-time',
                        value: '2026-04-03T09:00-07:00',
                        label: 'Boarding',
                        dateStyle: DateStyleEnum::Short,
                        timeStyle: DateStyleEnum::Short,
                        isRelative: true,
                    ),
                ],
                auxiliaryFields: [
                    new Field(key: 'seat', value: '7A', label: 'Seat'),
                    new Field(
                        key: 'passenger',
                        value: 'John Appleseed',
                        label: 'Passenger',
                        textAlignment: TextAlignmentEnum::Right,
                    ),
                ],
                backFields: [
                    new Field(key: 'terms', value: 'Terms and conditions apply.', label: 'Terms'),
                ],
                transitType: TransitTypeEnum::Air,
            ),
            barcodes: [
                new Barcode(altText: 'BP-001-SFO-LHR', format: BarcodeFormatEnum::QR, message: 'BP001SFOLHR', messageEncoding: 'iso-8859-1'),
            ],
            webServiceURL: 'https://example.com/passes/',
            authenticationToken: 'auth-token-123',
            backgroundColor: 'rgb(22, 55, 110)',
            foregroundColor: 'rgb(255, 255, 255)',
            labelColor: 'rgb(200, 200, 200)',
            logoText: 'Example Airlines',
            relevantDate: '2026-04-03T09:00-07:00',
            locations: [
                new Location(latitude: 37.6213, longitude: -122.3790, altitude: 10.0, relevantText: 'SFO Airport'),
            ],
            semantics: new SemanticTags(
                airlineCode: 'EX',
                flightNumber: 123,
                departureAirportCode: 'SFO',
                departureAirportName: 'San Francisco International',
                departureCityName: 'San Francisco',
                departureLocation: new SemanticLocation(latitude: 37.6213, longitude: -122.3790),
                departureLocationTimeZone: 'America/Los_Angeles',
                departureGate: 'F12',
                departureTerminal: '2',
                destinationAirportCode: 'LHR',
                destinationAirportName: 'London Heathrow',
                destinationCityName: 'London',
                destinationLocation: new SemanticLocation(latitude: 51.4700, longitude: -0.4543),
                destinationLocationTimeZone: 'Europe/London',
                originalDepartureDate: '2026-04-03T10:00-07:00',
                originalBoardingDate: '2026-04-03T09:00-07:00',
                originalArrivalDate: '2026-04-04T06:00+01:00',
                passengerName: new PersonNameComponents(givenName: 'John', familyName: 'Appleseed'),
                boardingGroup: 'A',
                boardingSequenceNumber: '042',
                seats: [new Seat(seatNumber: '7A', seatRow: '7', seatSection: 'Economy')],
                transitProvider: 'Example Airlines',
                membershipProgramName: 'SkyRewards',
                membershipProgramNumber: 'SR-123456',
                priorityStatus: 'Gold',
                ticketFareClass: 'Economy',
            ),
            preferredStyleSchemes: ['semanticBoardingPass', 'boardingPass'],
        );

        $data = $this->serializer->normalize($pass);

        self::assertSame(1, $data['formatVersion']);
        self::assertSame('pass.com.example.boarding', $data['passTypeIdentifier']);
        self::assertSame('BP-001', $data['serialNumber']);
        self::assertSame('A1B2C3D4E5', $data['teamIdentifier']);
        self::assertSame('Example Airlines', $data['organizationName']);
        self::assertSame('Boarding pass for SFO to LHR', $data['description']);

        self::assertArrayHasKey('boardingPass', $data);
        self::assertArrayNotHasKey('coupon', $data);
        self::assertArrayNotHasKey('eventTicket', $data);
        self::assertArrayNotHasKey('generic', $data);
        self::assertArrayNotHasKey('storeCard', $data);

        $structure = $data['boardingPass'];
        self::assertSame('PKTransitTypeAir', $structure['transitType']);
        self::assertCount(1, $structure['headerFields']);
        self::assertSame('gate', $structure['headerFields'][0]['key']);
        self::assertCount(2, $structure['primaryFields']);
        self::assertCount(1, $structure['secondaryFields']);
        self::assertSame('PKDateStyleShort', $structure['secondaryFields'][0]['dateStyle']);
        self::assertSame('PKDateStyleShort', $structure['secondaryFields'][0]['timeStyle']);
        self::assertTrue($structure['secondaryFields'][0]['isRelative']);
        self::assertCount(2, $structure['auxiliaryFields']);
        self::assertSame('PKTextAlignmentRight', $structure['auxiliaryFields'][1]['textAlignment']);
        self::assertCount(1, $structure['backFields']);

        self::assertCount(1, $data['barcodes']);
        self::assertSame('PKBarcodeFormatQR', $data['barcodes'][0]['format']);
        self::assertSame('BP-001-SFO-LHR', $data['barcodes'][0]['altText']);

        self::assertSame('https://example.com/passes/', $data['webServiceURL']);
        self::assertSame('auth-token-123', $data['authenticationToken']);
        self::assertSame('rgb(22, 55, 110)', $data['backgroundColor']);
        self::assertSame('rgb(255, 255, 255)', $data['foregroundColor']);
        self::assertSame('rgb(200, 200, 200)', $data['labelColor']);
        self::assertSame('Example Airlines', $data['logoText']);
        self::assertSame('2026-04-03T09:00-07:00', $data['relevantDate']);

        self::assertCount(1, $data['locations']);
        self::assertSame(37.6213, $data['locations'][0]['latitude']);
        self::assertSame(-122.3790, $data['locations'][0]['longitude']);
        self::assertSame(10.0, $data['locations'][0]['altitude']);
        self::assertSame('SFO Airport', $data['locations'][0]['relevantText']);

        $semantics = $data['semantics'];
        self::assertSame('EX', $semantics['airlineCode']);
        self::assertSame(123, $semantics['flightNumber']);
        self::assertSame('SFO', $semantics['departureAirportCode']);
        self::assertSame('San Francisco International', $semantics['departureAirportName']);
        self::assertSame('San Francisco', $semantics['departureCityName']);
        self::assertSame(37.6213, $semantics['departureLocation']['latitude']);
        self::assertSame('America/Los_Angeles', $semantics['departureLocationTimeZone']);
        self::assertSame('F12', $semantics['departureGate']);
        self::assertSame('2', $semantics['departureTerminal']);
        self::assertSame('LHR', $semantics['destinationAirportCode']);
        self::assertSame('London Heathrow', $semantics['destinationAirportName']);
        self::assertSame('London', $semantics['destinationCityName']);
        self::assertSame(51.4700, $semantics['destinationLocation']['latitude']);
        self::assertSame('Europe/London', $semantics['destinationLocationTimeZone']);
        self::assertSame('2026-04-03T10:00-07:00', $semantics['originalDepartureDate']);
        self::assertSame('2026-04-03T09:00-07:00', $semantics['originalBoardingDate']);
        self::assertSame('2026-04-04T06:00+01:00', $semantics['originalArrivalDate']);
        self::assertSame('John', $semantics['passengerName']['givenName']);
        self::assertSame('Appleseed', $semantics['passengerName']['familyName']);
        self::assertSame('A', $semantics['boardingGroup']);
        self::assertSame('042', $semantics['boardingSequenceNumber']);
        self::assertSame('7A', $semantics['seats'][0]['seatNumber']);
        self::assertSame('Example Airlines', $semantics['transitProvider']);
        self::assertSame('SkyRewards', $semantics['membershipProgramName']);
        self::assertSame('SR-123456', $semantics['membershipProgramNumber']);
        self::assertSame('Gold', $semantics['priorityStatus']);
        self::assertSame('Economy', $semantics['ticketFareClass']);

        self::assertSame(['semanticBoardingPass', 'boardingPass'], $data['preferredStyleSchemes']);
    }

    public function testCoupon(): void
    {
        $pass = new Pass(
            description: 'Winter holiday coupon',
            organizationName: 'My Shop',
            teamIdentifier: 'ABCD1234',
            passTypeIdentifier: 'pass.com.example.coupon',
            formatVersion: 1,
            serialNumber: 'CPN-001',
            passType: PassTypeEnum::Coupon,
            structure: new PassStructure(
                primaryFields: [
                    new Field(key: 'offer', value: '25% off', label: 'All holiday items.'),
                ],
                auxiliaryFields: [
                    new Field(
                        key: 'expires',
                        value: '2026-12-31T23:59-05:00',
                        label: 'EXPIRES',
                        dateStyle: DateStyleEnum::Short,
                        isRelative: true,
                    ),
                ],
                backFields: [
                    new Field(key: 'terms', value: 'Valid in-store only.', label: 'Terms and Conditions'),
                    new Field(key: 'support', value: '(800) 555-5555', label: 'Customer service'),
                ],
            ),
            barcodes: [
                new Barcode(altText: null, format: BarcodeFormatEnum::CODE128, message: 'CPN001', messageEncoding: 'iso-8859-1'),
            ],
            backgroundColor: 'rgb(34, 107, 72)',
            foregroundColor: 'rgb(245, 237, 95)',
            logoText: 'My Shop',
            expirationDate: '2026-12-31T23:59-05:00',
            locations: [
                new Location(latitude: 40.7128, longitude: -74.0060),
                new Location(latitude: 34.0522, longitude: -118.2437),
            ],
            suppressStripShine: true,
        );

        $data = $this->serializer->normalize($pass);

        self::assertArrayHasKey('coupon', $data);
        self::assertArrayNotHasKey('boardingPass', $data);

        $structure = $data['coupon'];
        self::assertArrayNotHasKey('transitType', $structure);
        self::assertCount(1, $structure['primaryFields']);
        self::assertSame('25% off', $structure['primaryFields'][0]['value']);
        self::assertCount(1, $structure['auxiliaryFields']);
        self::assertSame('PKDateStyleShort', $structure['auxiliaryFields'][0]['dateStyle']);
        self::assertTrue($structure['auxiliaryFields'][0]['isRelative']);
        self::assertCount(2, $structure['backFields']);

        self::assertCount(1, $data['barcodes']);
        self::assertSame('PKBarcodeFormatCode128', $data['barcodes'][0]['format']);
        self::assertNull($data['barcodes'][0]['altText']);

        self::assertSame('rgb(34, 107, 72)', $data['backgroundColor']);
        self::assertSame('rgb(245, 237, 95)', $data['foregroundColor']);
        self::assertSame('My Shop', $data['logoText']);
        self::assertSame('2026-12-31T23:59-05:00', $data['expirationDate']);
        self::assertTrue($data['suppressStripShine']);

        self::assertCount(2, $data['locations']);
        self::assertSame(40.7128, $data['locations'][0]['latitude']);
        self::assertArrayNotHasKey('altitude', $data['locations'][0]);
    }

    public function testEventTicket(): void
    {
        $pass = new Pass(
            description: 'Concert ticket',
            organizationName: 'Live Events Inc.',
            teamIdentifier: 'T5742Z534D',
            passTypeIdentifier: 'pass.com.example.event',
            formatVersion: 1,
            serialNumber: 'EVT-001',
            passType: PassTypeEnum::EventTicket,
            structure: new PassStructure(
                primaryFields: [
                    new Field(key: 'event-name', value: 'The Hectic Glow in concert', label: 'Event'),
                ],
                secondaryFields: [
                    new Field(
                        key: 'doors-open',
                        value: '2026-08-10T19:30-06:00',
                        label: 'Doors open',
                        dateStyle: DateStyleEnum::Medium,
                        timeStyle: DateStyleEnum::Short,
                    ),
                    new Field(
                        key: 'section',
                        value: 5,
                        label: 'Seating section',
                        numberStyle: NumberStyleEnum::SpellOut,
                        textAlignment: TextAlignmentEnum::Right,
                    ),
                ],
                auxiliaryFields: [
                    new Field(key: 'row', value: '02', label: 'Row', row: 0),
                    new Field(key: 'seat', value: '9', label: 'Seat', row: 0),
                ],
                backFields: [
                    new Field(key: 'venue-address', value: '123 Music Ave, Denver, CO', label: 'Venue'),
                ],
            ),
            barcodes: [
                new Barcode(altText: null, format: BarcodeFormatEnum::AZTEC, message: 'EVT001SEAT9', messageEncoding: 'iso-8859-1'),
            ],
            backgroundColor: 'rgb(215, 154, 172)',
            foregroundColor: 'rgb(255, 255, 255)',
            labelColor: 'rgb(255, 255, 255)',
            groupingIdentifier: 'Opening night',
            relevantDates: [
                new RelevantDate(startDate: '2026-08-10T18:00-06:00', endDate: '2026-08-10T23:00-06:00'),
            ],
            semantics: new SemanticTags(
                eventName: 'The Hectic Glow in concert',
                eventType: EventTypeEnum::LivePerformance,
                eventStartDate: '2026-08-10T19:30-06:00',
                eventStartDateInfo: new EventDateInfo(
                    startDate: '2026-08-10T19:30-06:00',
                    timeZone: 'America/Denver',
                ),
                venueName: 'Red Rocks Amphitheatre',
                venueRegionName: 'Denver',
                venueRoom: 'Main Stage',
                venueLocation: new SemanticLocation(latitude: 39.6654, longitude: -105.2057),
                performerNames: ['The Hectic Glow', 'Opening Act'],
                artistIDs: ['artist-123', 'artist-456'],
                seats: [
                    new Seat(seatNumber: '9', seatRow: '02', seatSection: '117', seatType: 'General'),
                ],
                admissionLevel: 'VIP',
                attendeeName: 'Jane Doe',
            ),
            preferredStyleSchemes: ['posterEventTicket', 'eventTicket'],
        );

        $data = $this->serializer->normalize($pass);

        self::assertArrayHasKey('eventTicket', $data);
        self::assertArrayNotHasKey('boardingPass', $data);

        $structure = $data['eventTicket'];
        self::assertCount(1, $structure['primaryFields']);
        self::assertCount(2, $structure['secondaryFields']);
        self::assertSame('PKDateStyleMedium', $structure['secondaryFields'][0]['dateStyle']);
        self::assertSame('PKDateStyleShort', $structure['secondaryFields'][0]['timeStyle']);
        self::assertSame('PKNumberStyleSpellOut', $structure['secondaryFields'][1]['numberStyle']);
        self::assertSame('PKTextAlignmentRight', $structure['secondaryFields'][1]['textAlignment']);
        self::assertSame(5, $structure['secondaryFields'][1]['value']);
        self::assertCount(2, $structure['auxiliaryFields']);
        self::assertSame(0, $structure['auxiliaryFields'][0]['row']);

        self::assertSame('Opening night', $data['groupingIdentifier']);

        self::assertCount(1, $data['relevantDates']);
        self::assertSame('2026-08-10T18:00-06:00', $data['relevantDates'][0]['startDate']);
        self::assertSame('2026-08-10T23:00-06:00', $data['relevantDates'][0]['endDate']);

        $semantics = $data['semantics'];
        self::assertSame('The Hectic Glow in concert', $semantics['eventName']);
        self::assertSame('PKEventTypeLivePerformance', $semantics['eventType']);
        self::assertSame('2026-08-10T19:30-06:00', $semantics['eventStartDate']);
        self::assertSame('2026-08-10T19:30-06:00', $semantics['eventStartDateInfo']['startDate']);
        self::assertSame('America/Denver', $semantics['eventStartDateInfo']['timeZone']);
        self::assertSame('Red Rocks Amphitheatre', $semantics['venueName']);
        self::assertSame('Denver', $semantics['venueRegionName']);
        self::assertSame('Main Stage', $semantics['venueRoom']);
        self::assertSame(39.6654, $semantics['venueLocation']['latitude']);
        self::assertSame(-105.2057, $semantics['venueLocation']['longitude']);
        self::assertSame(['The Hectic Glow', 'Opening Act'], $semantics['performerNames']);
        self::assertSame(['artist-123', 'artist-456'], $semantics['artistIDs']);
        self::assertSame('9', $semantics['seats'][0]['seatNumber']);
        self::assertSame('02', $semantics['seats'][0]['seatRow']);
        self::assertSame('117', $semantics['seats'][0]['seatSection']);
        self::assertSame('VIP', $semantics['admissionLevel']);
        self::assertSame('Jane Doe', $semantics['attendeeName']);

        self::assertSame(['posterEventTicket', 'eventTicket'], $data['preferredStyleSchemes']);
    }

    public function testGeneric(): void
    {
        $pass = new Pass(
            description: 'Gym membership pass',
            organizationName: 'My Gym',
            teamIdentifier: 'ABCD1234',
            passTypeIdentifier: 'pass.com.example.membership',
            formatVersion: 1,
            serialNumber: 'GEN-001',
            passType: PassTypeEnum::Generic,
            structure: new PassStructure(
                primaryFields: [
                    new Field(key: 'memberName', value: 'Maria Ruiz', label: 'Name'),
                ],
                secondaryFields: [
                    new Field(key: 'memberNumber', value: '7337', label: 'Member Number'),
                ],
                auxiliaryFields: [
                    new Field(
                        key: 'memberSince',
                        value: '2026-01-02T00:00-07:00',
                        label: 'Joined',
                        dateStyle: DateStyleEnum::Short,
                    ),
                ],
                backFields: [
                    new Field(key: 'support', value: '(800) 555-5555', label: 'Customer service'),
                    new Field(
                        key: 'terms',
                        value: 'Membership Terms and Conditions.',
                        label: 'Terms',
                        changeMessage: 'Terms updated: %@',
                        attributedValue: '<a href="https://example.com/terms">View full terms</a>',
                    ),
                ],
            ),
            barcodes: [
                new Barcode(altText: '7337', format: BarcodeFormatEnum::PDF417, message: 'GEN0017337', messageEncoding: 'iso-8859-1'),
            ],
            webServiceURL: 'https://example.com/passes/',
            authenticationToken: 'xyz-token',
            backgroundColor: 'rgb(245, 197, 67)',
            foregroundColor: 'rgb(0, 0, 0)',
            logoText: 'My Gym',
            locations: [
                new Location(latitude: 37.3318, longitude: -122.0312),
            ],
            beacons: [
                new Beacon(proximityUUID: 'F8F589E9-C07E-58B0-AEAB-A36BE4D48FAC', major: 1, minor: 100, relevantText: "You're near the gym"),
            ],
            maxDistance: 500.0,
            sharingProhibited: true,
            userInfo: ['internalId' => 'usr-42'],
        );

        $data = $this->serializer->normalize($pass);

        self::assertArrayHasKey('generic', $data);
        self::assertArrayNotHasKey('storeCard', $data);

        $structure = $data['generic'];
        self::assertCount(1, $structure['primaryFields']);
        self::assertSame('Maria Ruiz', $structure['primaryFields'][0]['value']);
        self::assertCount(1, $structure['secondaryFields']);
        self::assertCount(1, $structure['auxiliaryFields']);
        self::assertSame('PKDateStyleShort', $structure['auxiliaryFields'][0]['dateStyle']);
        self::assertCount(2, $structure['backFields']);
        self::assertSame('Terms updated: %@', $structure['backFields'][1]['changeMessage']);
        self::assertSame('<a href="https://example.com/terms">View full terms</a>', $structure['backFields'][1]['attributedValue']);

        self::assertCount(1, $data['barcodes']);
        self::assertSame('PKBarcodeFormatPDF417', $data['barcodes'][0]['format']);
        self::assertSame('7337', $data['barcodes'][0]['altText']);

        self::assertSame('https://example.com/passes/', $data['webServiceURL']);
        self::assertSame('xyz-token', $data['authenticationToken']);
        self::assertSame('rgb(245, 197, 67)', $data['backgroundColor']);
        self::assertSame('rgb(0, 0, 0)', $data['foregroundColor']);
        self::assertSame('My Gym', $data['logoText']);

        self::assertCount(1, $data['locations']);
        self::assertSame(37.3318, $data['locations'][0]['latitude']);
        self::assertArrayNotHasKey('altitude', $data['locations'][0]);
        self::assertArrayNotHasKey('relevantText', $data['locations'][0]);

        self::assertCount(1, $data['beacons']);
        self::assertSame('F8F589E9-C07E-58B0-AEAB-A36BE4D48FAC', $data['beacons'][0]['proximityUUID']);
        self::assertSame(1, $data['beacons'][0]['major']);
        self::assertSame(100, $data['beacons'][0]['minor']);
        self::assertSame("You're near the gym", $data['beacons'][0]['relevantText']);

        self::assertSame(500.0, $data['maxDistance']);
        self::assertTrue($data['sharingProhibited']);
        self::assertSame(['internalId' => 'usr-42'], $data['userInfo']);
    }

    public function testStoreCard(): void
    {
        $pass = new Pass(
            description: 'Coffee shop loyalty card',
            organizationName: 'My Coffee Co.',
            teamIdentifier: 'ABCD1234',
            passTypeIdentifier: 'pass.com.example.loyalty',
            formatVersion: 1,
            serialNumber: 'SC-001',
            passType: PassTypeEnum::StoreCard,
            structure: new PassStructure(
                primaryFields: [
                    new Field(key: 'balance', value: 10, label: 'Rewards Value', currencyCode: 'USD'),
                ],
                auxiliaryFields: [
                    new Field(key: 'status', value: 'Coffee Champ', label: 'Coffee Status'),
                    new Field(key: 'points', value: 75, label: 'Points'),
                ],
                backFields: [
                    new Field(key: 'terms', value: 'Rewards terms apply.', label: 'Terms'),
                ],
            ),
            barcodes: [
                new Barcode(altText: null, format: BarcodeFormatEnum::QR, message: 'SC001', messageEncoding: 'iso-8859-1'),
            ],
            nfc: new Nfc(
                message: 'loyalty-card-sc001',
                encryptionPublicKey: 'MIIBI...',
                requiresAuthentication: true,
            ),
            associatedStoreIdentifiers: [123456789],
            appLaunchURL: 'myapp://loyalty/SC-001',
            backgroundColor: 'rgb(24, 44, 82)',
            foregroundColor: 'rgb(222, 173, 40)',
            logoText: 'My Coffee Shop',
            voided: false,
            semantics: new SemanticTags(
                balance: new CurrencyAmount(amount: '10.00', currencyCode: 'USD'),
                wifiAccess: [
                    new WifiNetwork(ssid: 'CoffeeShop-Guest', password: 'welcome123'),
                ],
            ),
        );

        $data = $this->serializer->normalize($pass);

        self::assertArrayHasKey('storeCard', $data);
        self::assertArrayNotHasKey('generic', $data);

        $structure = $data['storeCard'];
        self::assertArrayNotHasKey('transitType', $structure);
        self::assertCount(1, $structure['primaryFields']);
        self::assertSame(10, $structure['primaryFields'][0]['value']);
        self::assertSame('USD', $structure['primaryFields'][0]['currencyCode']);
        self::assertCount(2, $structure['auxiliaryFields']);
        self::assertSame('Coffee Champ', $structure['auxiliaryFields'][0]['value']);
        self::assertSame(75, $structure['auxiliaryFields'][1]['value']);
        self::assertCount(1, $structure['backFields']);

        self::assertSame('loyalty-card-sc001', $data['nfc']['message']);
        self::assertSame('MIIBI...', $data['nfc']['encryptionPublicKey']);
        self::assertTrue($data['nfc']['requiresAuthentication']);

        self::assertSame([123456789], $data['associatedStoreIdentifiers']);
        self::assertSame('myapp://loyalty/SC-001', $data['appLaunchURL']);
        self::assertSame('rgb(24, 44, 82)', $data['backgroundColor']);
        self::assertSame('rgb(222, 173, 40)', $data['foregroundColor']);
        self::assertSame('My Coffee Shop', $data['logoText']);
        self::assertFalse($data['voided']);

        $semantics = $data['semantics'];
        self::assertSame('10.00', $semantics['balance']['amount']);
        self::assertSame('USD', $semantics['balance']['currencyCode']);
        self::assertCount(1, $semantics['wifiAccess']);
        self::assertSame('CoffeeShop-Guest', $semantics['wifiAccess'][0]['ssid']);
        self::assertSame('welcome123', $semantics['wifiAccess'][0]['password']);
    }
}
