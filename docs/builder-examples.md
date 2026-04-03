# Wallet Kit — Builder examples

This page shows one **end-to-end example** per vertical supported by [`WalletPass`](../src/Builder/WalletPass.php). Each example assumes you already configured a [`WalletPlatformContext`](../src/Builder/WalletPlatformContext.php) with your real Apple and Google identifiers.

After `build()`, you get a [`BuiltWalletPass`](../src/Builder/BuiltWalletPass.php):

- `$built->apple()` → Apple [`Pass`](../src/Pass/Apple/Model/Pass.php) for `pass.json`
- `$built->google()->issuerClass` / `$built->google()->passObject` → Google class and object for the Wallet API

Serialize with **Symfony Serializer** and the normalizers from this package (see [`tests/Builder/BuilderTestSerializerFactory.php`](../tests/Builder/BuilderTestSerializerFactory.php) for a full list).

---

## Shared context (all examples)

```php
<?php

declare(strict_types=1);

use Jolicode\WalletKit\Builder\WalletPlatformContext;
use Jolicode\WalletKit\Pass\Android\Model\Shared\ReviewStatusEnum;
use Jolicode\WalletKit\Pass\Android\Model\Shared\StateEnum;

$context = new WalletPlatformContext(
    appleTeamIdentifier: 'YOUR_TEAM_ID',
    applePassTypeIdentifier: 'pass.com.example.app',
    appleSerialNumber: 'UNIQUE-SERIAL-001',
    appleOrganizationName: 'Example Airlines',
    appleDescription: 'Boarding pass SFO → LHR',
    googleClassId: '3388000000012345.example_flight_class',
    googleObjectId: '3388000000012345.example_flight_object',
    defaultGoogleReviewStatus: ReviewStatusEnum::APPROVED,
    defaultGoogleObjectState: StateEnum::ACTIVE,
);
```

---

## 1. Generic pass

**Use case:** membership card, insurance card, or any pass that does not fit a specialized vertical.

```php
<?php

declare(strict_types=1);

use Jolicode\WalletKit\Builder\WalletPass;
use Jolicode\WalletKit\Pass\Android\Model\Generic\GenericTypeEnum;
use Jolicode\WalletKit\Pass\Apple\Model\Barcode;
use Jolicode\WalletKit\Pass\Apple\Model\BarcodeFormatEnum;
use Jolicode\WalletKit\Pass\Apple\Model\Field;
use Jolicode\WalletKit\Pass\Apple\Model\PassStructure;

$built = WalletPass::generic($context)
    ->withPassStructure(new PassStructure(
        primaryFields: [
            new Field(key: 'member', value: 'Jane Doe', label: 'Member'),
        ],
        secondaryFields: [
            new Field(key: 'id', value: 'MEM-8842', label: 'ID'),
        ],
    ))
    ->withGenericType(GenericTypeEnum::GYM_MEMBERSHIP)
    ->withGoogleCardTitle('Gym membership')
    ->addAppleBarcode(new Barcode(
        altText: 'Member ID',
        format: BarcodeFormatEnum::QR,
        message: 'MEM8842',
        messageEncoding: 'utf-8',
    ))
    ->withGoogleHexBackgroundColor('#1a237e')
    ->withGrouping('membership-2026', 0)
    ->build();
```

---

## 2. Offer / coupon

**Use case:** promotional offer redeemable in store, online, or both.

```php
<?php

declare(strict_types=1);

use Jolicode\WalletKit\Builder\WalletPass;
use Jolicode\WalletKit\Pass\Android\Model\Offer\RedemptionChannelEnum;
use Jolicode\WalletKit\Pass\Apple\Model\Barcode;
use Jolicode\WalletKit\Pass\Apple\Model\BarcodeFormatEnum;

$built = WalletPass::offer(
    $context,
    title: '20% off next visit',
    provider: 'Example Coffee',
    redemptionChannel: RedemptionChannelEnum::INSTORE,
)
    ->withBackgroundColorRgb('rgb(40, 80, 120)')
    ->addAppleBarcode(new Barcode(
        altText: 'Promo',
        format: BarcodeFormatEnum::PDF417,
        message: 'PROMO-2026-04',
        messageEncoding: 'iso-8859-1',
    ))
    ->withGoogleValidityWindow('2026-04-01T00:00:00', '2026-06-30T23:59:59')
    ->build();
```

---

## 3. Loyalty / store card

**Use case:** points program or store loyalty card (Apple `storeCard`, Google `Loyalty`).

```php
<?php

declare(strict_types=1);

use Jolicode\WalletKit\Builder\WalletPass;

$built = WalletPass::loyalty($context, programName: 'Gold Rewards')
    ->withAccount(accountName: 'Alex Martin', accountId: 'GLD-991122')
    ->withAppleWebService(
        url: 'https://api.example.com/v1/passes/',
        authenticationToken: 'opaque-shared-secret',
    )
    ->withGrouping('loyalty-tier-gold', 2)
    ->build();
```

---

## 4. Event ticket

**Use case:** concert, match, or venue ticket.

```php
<?php

declare(strict_types=1);

use Jolicode\WalletKit\Builder\WalletPass;
use Jolicode\WalletKit\Pass\Apple\Model\Barcode;
use Jolicode\WalletKit\Pass\Apple\Model\BarcodeFormatEnum;

$built = WalletPass::eventTicket($context, eventName: 'Indie Fest 2026')
    ->withTicketHolderName('Sam Rivera')
    ->withTicketNumber('EVT-7F3A-9910')
    ->addAppleBarcode(new Barcode(
        altText: 'Entry',
        format: BarcodeFormatEnum::QR,
        message: 'EVT7F3A9910',
        messageEncoding: 'utf-8',
    ))
    ->build();
```

---

## 5. Flight (boarding pass)

**Use case:** airline boarding pass (Apple `boardingPass` with air transit, Google `Flight`).

```php
<?php

declare(strict_types=1);

use Jolicode\WalletKit\Builder\WalletPass;
use Jolicode\WalletKit\Pass\Android\Model\Flight\AirportInfo;
use Jolicode\WalletKit\Pass\Android\Model\Flight\BoardingAndSeatingInfo;
use Jolicode\WalletKit\Pass\Android\Model\Flight\FlightCarrier;
use Jolicode\WalletKit\Pass\Android\Model\Flight\FlightHeader;
use Jolicode\WalletKit\Pass\Android\Model\Flight\ReservationInfo;

$built = WalletPass::flight(
    $context,
    passengerName: 'Jordan Smith',
    reservationInfo: new ReservationInfo(
        confirmationCode: 'XK4P2Q',
        eticketNumber: '0123456789',
    ),
    flightHeader: new FlightHeader(
        carrier: new FlightCarrier(carrierIataCode: 'ZZ'),
        flightNumber: '412',
    ),
    origin: new AirportInfo(airportIataCode: 'CDG', gate: 'K42'),
    destination: new AirportInfo(airportIataCode: 'JFK', terminal: '4'),
)
    ->withBoardingAndSeatingInfo(new BoardingAndSeatingInfo(
        boardingGroup: '2',
        sequenceNumber: '042',
    ))
    ->withAppleForegroundColor('rgb(255, 255, 255)')
    ->build();
```

---

## 6. Transit (bus, rail, ferry, …)

**Use case:** ground or sea transit ticket (Apple `boardingPass` with mapped `transitType`, Google `Transit`).

```php
<?php

declare(strict_types=1);

use Jolicode\WalletKit\Builder\WalletPass;
use Jolicode\WalletKit\Pass\Android\Model\Shared\StateEnum;
use Jolicode\WalletKit\Pass\Android\Model\Transit\TransitTypeEnum;
use Jolicode\WalletKit\Pass\Android\Model\Transit\TripTypeEnum;

$built = WalletPass::transit(
    $context,
    transitType: TransitTypeEnum::RAIL,
    tripType: TripTypeEnum::ONE_WAY,
)
    ->withTicketNumber('TCK-RAIL-884499')
    ->withGoogleObjectState(StateEnum::ACTIVE)
    ->build();
```

Google `TransitTypeEnum` cases (`BUS`, `RAIL`, `TRAM`, `FERRY`, …) are mapped to the closest Apple [`TransitTypeEnum`](../src/Pass/Apple/Model/TransitTypeEnum.php) (for example `RAIL` / `TRAM` → `TRAIN`).

---

## 7. Gift card

**Use case:** Google **Gift card** object. Apple has no dedicated gift-card type: the builder emits a **`storeCard`** with card-oriented fields.

```php
<?php

declare(strict_types=1);

use Jolicode\WalletKit\Builder\WalletPass;

$built = WalletPass::giftCard($context, cardNumber: '6034932523842700')
    ->withPin('4829')
    ->withGoogleHexBackgroundColor('#4e342e')
    ->build();
```

---

## Portable options (all builders)

These methods come from [`CommonWalletBuilderTrait`](../src/Builder/CommonWalletBuilderTrait.php) and are available on every vertical builder:

| Method | Role |
|--------|------|
| `addAppleBarcode` | Adds a barcode on the Apple pass; the **first** one is mirrored on Google unless you override. |
| `withGoogleBarcodeOverride` | Forces the Google object barcode. |
| `withAppleBackgroundColor` / `withGoogleHexBackgroundColor` | Colors per platform. |
| `withBackgroundColorRgb` | Sets Apple RGB and derives Google hex when possible. |
| `withAppleForegroundColor` / `withAppleLabelColor` | Apple-only styling. |
| `withGrouping` | Apple `groupingIdentifier` + Google `GroupingInfo`. |
| `withAppleWebService` | Apple pass update web service URL and token. |
| `withAppleAppLaunchUrl` / `withAppleAssociatedStoreIdentifiers` | Associated app hints (Apple). |
| `withAppleExpiration` | Apple expiration / voided. |
| `withGoogleValidTimeInterval` / `withGoogleValidityWindow` | Google validity window. |
| `withGoogleReviewStatus` / `withGoogleObjectState` | Overrides context defaults for Google class/object lifecycle. |
| `withAppLinkData` / `withGoogleLinksModuleData` | Links and app deep links (Google; Apple where mapped). |
| `mutateApple(callable)` | Escape hatch to tweak the Apple `Pass` before `build()` returns. |

---

## Limitations

- The library does **not** sign `.pkpass` bundles or call Google Wallet REST APIs.
- Apple and Google models differ: not every field exists on both sides. Use `mutateApple` or adjust the returned Google class/object after `build()` for platform-specific details.
