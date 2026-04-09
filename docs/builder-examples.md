# Wallet Kit — Builder examples

## Contents

- [Shared context (dual-platform examples)](#shared-context-dual-platform-examples)
- [Apple-only and Google-only snippets](#apple-only-and-google-only-snippets)
- [1. Generic pass](#1-generic-pass)
- [2. Offer / coupon](#2-offer--coupon)
- [3. Loyalty / store card](#3-loyalty--store-card)
- [4. Event ticket](#4-event-ticket)
- [5. Flight (boarding pass)](#5-flight-boarding-pass)
- [6. Transit (bus, rail, ferry, …)](#6-transit-bus-rail-ferry-)
- [7. Gift card](#7-gift-card)
- [Portable options (all builders)](#portable-options-all-builders)
- [Limitations](#limitations)

This page shows one **end-to-end example** per vertical supported by [`WalletPass`](../src/Builder/WalletPass.php). Examples below use a **dual-platform** context; you can target any combination by chaining [`->withApple(...)`](../src/Builder/WalletPlatformContext.php), [`->withGoogle(...)`](../src/Builder/WalletPlatformContext.php), and/or [`->withSamsung(...)`](../src/Builder/WalletPlatformContext.php).

After `build()`, you get a [`BuiltWalletPass`](../src/Builder/BuiltWalletPass.php):

- `$built->apple()` → Apple [`Pass`](../src/Pass/Apple/Model/Pass.php) for `pass.json` (throws [`ApplePassNotAvailableException`](../src/Exception/ApplePassNotAvailableException.php) if the context had no Apple slice)
- `$built->google()->issuerClass` / `$built->google()->passObject` → Google class and object (throws [`GoogleWalletPairNotAvailableException`](../src/Exception/GoogleWalletPairNotAvailableException.php) if there was no Google slice)
- `$built->samsung()` → Samsung [`Card`](../src/Pass/Samsung/Model/Card.php) envelope (throws [`SamsungCardNotAvailableException`](../src/Exception/SamsungCardNotAvailableException.php) if there was no Samsung slice)

Serialize with **Symfony Serializer** and the normalizers from this package (see [`tests/Builder/BuilderTestSerializerFactory.php`](../tests/Builder/BuilderTestSerializerFactory.php) for a full list).

---

## Shared context (dual-platform examples)

```php
<?php

declare(strict_types=1);

use Jolicode\WalletKit\Builder\WalletPlatformContext;
use Jolicode\WalletKit\Pass\Android\Model\Shared\ReviewStatusEnum;
use Jolicode\WalletKit\Pass\Android\Model\Shared\StateEnum;

$context = (new WalletPlatformContext())
    ->withApple(
        teamIdentifier: 'YOUR_TEAM_ID',
        passTypeIdentifier: 'pass.com.example.app',
        serialNumber: 'UNIQUE-SERIAL-001',
        organizationName: 'Example Airlines',
        description: 'Boarding pass SFO → LHR',
    )
    ->withGoogle(
        classId: '3388000000012345.example_flight_class',
        objectId: '3388000000012345.example_flight_object',
        defaultReviewStatus: ReviewStatusEnum::APPROVED,
        defaultObjectState: StateEnum::ACTIVE,
    );
```

---

## Apple-only and Google-only snippets

**Apple-only** (no Google or Samsung needed). After `build()`, use only `$built->apple()`; `$built->google()` and `$built->samsung()` throw.

```php
use Jolicode\WalletKit\Builder\WalletPlatformContext;
use Jolicode\WalletKit\Builder\WalletPass;
use Jolicode\WalletKit\Pass\Apple\Model\Field;
use Jolicode\WalletKit\Pass\Apple\Model\PassStructure;
use Jolicode\WalletKit\Pass\Android\Model\Generic\GenericTypeEnum;

$appleContext = (new WalletPlatformContext())->withApple(
    teamIdentifier: 'YOUR_TEAM_ID',
    passTypeIdentifier: 'pass.com.example.app',
    serialNumber: 'SN-APPLE-ONLY',
    organizationName: 'Example Org',
    description: 'Membership',
);

$built = WalletPass::generic($appleContext)
    ->withPassStructure(new PassStructure(
        primaryFields: [new Field(key: 'member', value: 'Jane', label: 'Member')],
    ))
    ->withGenericType(GenericTypeEnum::GYM_MEMBERSHIP)
    ->build();

$pass = $built->apple();
```

**Google-only** (requires `issuerName` for class payloads when no Apple context provides an organization name). After `build()`, use `$built->google()`; `$built->apple()` throws. You can still call `addAppleBarcode()` to supply a barcode for the Google object.

```php
use Jolicode\WalletKit\Builder\WalletPlatformContext;
use Jolicode\WalletKit\Builder\WalletPass;
use Jolicode\WalletKit\Pass\Android\Model\Offer\RedemptionChannelEnum;
use Jolicode\WalletKit\Pass\Apple\Model\Barcode;
use Jolicode\WalletKit\Pass\Apple\Model\BarcodeFormatEnum;

$googleContext = (new WalletPlatformContext())->withGoogle(
    classId: '3388000000012345.example_offer_class',
    objectId: '3388000000012345.example_offer_object',
    issuerName: 'Example Shop',
);

$built = WalletPass::offer(
    $googleContext,
    title: '10% off',
    provider: 'Example Shop',
    redemptionChannel: RedemptionChannelEnum::INSTORE,
)->addAppleBarcode(new Barcode(
    altText: 'Promo',
    format: BarcodeFormatEnum::QR,
    message: 'SAVE10',
    messageEncoding: 'utf-8',
))->build();

$pair = $built->google();
```

**Samsung-only** (Samsung requires `appLinkLogo`, `appLinkName`, `appLinkData` on all card types). After `build()`, use `$built->samsung()`; `$built->apple()` and `$built->google()` throw.

```php
use Jolicode\WalletKit\Builder\WalletPlatformContext;
use Jolicode\WalletKit\Builder\WalletPass;
use Jolicode\WalletKit\Pass\Android\Model\Offer\RedemptionChannelEnum;
use Jolicode\WalletKit\Pass\Apple\Model\Barcode;
use Jolicode\WalletKit\Pass\Apple\Model\BarcodeFormatEnum;

$samsungContext = (new WalletPlatformContext())->withSamsung(
    refId: 'coupon-samsung-001',
    appLinkLogo: 'https://example.com/logo.png',
    appLinkName: 'Example Shop',
    appLinkData: 'https://example.com',
);

$built = WalletPass::offer(
    $samsungContext,
    title: '10% off',
    provider: 'Example Shop',
    redemptionChannel: RedemptionChannelEnum::INSTORE,
)->addAppleBarcode(new Barcode(
    altText: 'Promo',
    format: BarcodeFormatEnum::QR,
    message: 'SAVE10',
    messageEncoding: 'utf-8',
))->build();

$card = $built->samsung();
```

**All three platforms** — Apple + Google + Samsung in a single build.

```php
use Jolicode\WalletKit\Builder\WalletPlatformContext;
use Jolicode\WalletKit\Builder\WalletPass;
use Jolicode\WalletKit\Pass\Android\Model\Offer\RedemptionChannelEnum;
use Jolicode\WalletKit\Pass\Android\Model\Shared\ReviewStatusEnum;
use Jolicode\WalletKit\Pass\Android\Model\Shared\StateEnum;

$allContext = (new WalletPlatformContext())
    ->withApple(
        teamIdentifier: 'YOUR_TEAM_ID',
        passTypeIdentifier: 'pass.com.example.app',
        serialNumber: 'SN-ALL-001',
        organizationName: 'Example Shop',
        description: 'Promotional offer',
    )
    ->withGoogle(
        classId: '3388000000012345.example_offer_class',
        objectId: '3388000000012345.example_offer_object',
        defaultReviewStatus: ReviewStatusEnum::APPROVED,
        defaultObjectState: StateEnum::ACTIVE,
    )
    ->withSamsung(
        refId: 'offer-samsung-001',
        appLinkLogo: 'https://example.com/logo.png',
        appLinkName: 'Example Shop',
        appLinkData: 'https://example.com',
    );

$built = WalletPass::offer($allContext, '20% off', 'Example Shop', RedemptionChannelEnum::BOTH)->build();

$applePass = $built->apple();
$googlePair = $built->google();
$samsungCard = $built->samsung();
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
| `mutateSamsung(callable)` | Escape hatch to tweak the Samsung `Card` before `build()` returns. |

---

## Limitations

- The library does **not** sign `.pkpass` bundles, call Google Wallet REST APIs, or tokenize Samsung Wallet payloads.
- Apple, Google, and Samsung models differ: not every field exists on all sides. Use `mutateApple`, `mutateSamsung`, or adjust the returned Google class/object after `build()` for platform-specific details.
- A [`WalletPlatformContext`](../src/Builder/WalletPlatformContext.php) with **no** platform slice will produce a `BuiltWalletPass` where all accessors throw. Google contexts without an `issuerName` must have an Apple context to fall back on (via `organizationName`).
- Samsung **Digital ID** and **Pay As You Go** card types have no Apple/Google equivalent and are not exposed through `WalletPass` factory methods. Build them directly via the Samsung model classes under `src/Pass/Samsung/Model/`.
