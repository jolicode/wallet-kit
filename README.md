<h1 align="center">
  <a href="https://github.com/jolicode/wallet-kit">Wallet Kit</a>
  <br />
  <sub><em><h6>Typed PHP models and Symfony Serializer normalizers for Apple Wallet and Google Wallet payloads.</h6></em></sub>
</h1>

<div align="center">

[![PHP Version Require](http://poser.pugx.org/jolicode/wallet-kit/require/php)](https://packagist.org/packages/jolicode/wallet-kit)
[![Monthly Downloads](http://poser.pugx.org/jolicode/wallet-kit/d/monthly)](https://packagist.org/packages/jolicode/wallet-kit)

</div>

## Overview

Wallet Kit helps you build the **JSON payloads** wallet platforms expect. It focuses on **modeling and normalization** (via Symfony Serializer): it does **not** sign Apple passes, bundle `.pkpass` files, or call Google Wallet APIs.

- **PHP** 8.5+
- **symfony/serializer** ^8.0

## Dual-platform builder (highlight)

The **`Jolicode\WalletKit\Builder`** namespace provides a **fluent API** that builds **both** an Apple [`Pass`](src/Pass/Apple/Model/Pass.php) and the matching Google Wallet **class + object** in one go. Entry point: [`WalletPass`](src/Builder/WalletPass.php) (`generic`, `offer`, `loyalty`, `eventTicket`, `flight`, `transit`, `giftCard`).

1. Create a [`WalletPlatformContext`](src/Builder/WalletPlatformContext.php) with your Apple identifiers (team, pass type, serial, organization, description) and Google IDs (`classId`, `objectId`).
2. Chain portable options (barcodes, colors, grouping, validity, web service URL, …) via [`CommonWalletBuilderTrait`](src/Builder/CommonWalletBuilderTrait.php).
3. Call **`build()`** → [`BuiltWalletPass`](src/Builder/BuiltWalletPass.php): `apple()` for `pass.json`, `google()` for the [`GoogleWalletPair`](src/Builder/GoogleWalletPair.php) (issuer class + holder object).

**Full cookbook:** [docs/builder-examples.md](docs/builder-examples.md) — **one worked example per vertical** (generic, offer, loyalty, event ticket, flight, transit, gift card).

### Example A — coupon / offer (both stores)

```php
use Jolicode\WalletKit\Builder\WalletPass;
use Jolicode\WalletKit\Builder\WalletPlatformContext;
use Jolicode\WalletKit\Pass\Android\Model\Offer\RedemptionChannelEnum;
use Jolicode\WalletKit\Pass\Android\Model\Shared\ReviewStatusEnum;
use Jolicode\WalletKit\Pass\Android\Model\Shared\StateEnum;
use Jolicode\WalletKit\Pass\Apple\Model\Barcode;
use Jolicode\WalletKit\Pass\Apple\Model\BarcodeFormatEnum;

$context = new WalletPlatformContext(
    appleTeamIdentifier: 'ABCDE12345',
    applePassTypeIdentifier: 'pass.com.example.coupon',
    appleSerialNumber: 'COUPON-001',
    appleOrganizationName: 'Example Shop',
    appleDescription: 'Spring sale coupon',
    googleClassId: '3388000000012345.example_offer_class',
    googleObjectId: '3388000000012345.example_offer_object',
    defaultGoogleReviewStatus: ReviewStatusEnum::Approved,
    defaultGoogleObjectState: StateEnum::Active,
);

$built = WalletPass::offer(
    $context,
    title: '15% off',
    provider: 'Example Shop',
    redemptionChannel: RedemptionChannelEnum::Both,
)
    ->withBackgroundColorRgb('rgb(30, 60, 90)')
    ->addAppleBarcode(new Barcode(
        altText: 'Coupon',
        format: BarcodeFormatEnum::QR,
        message: 'SAVE15-2026',
        messageEncoding: 'utf-8',
    ))
    ->build();

// $built->apple()     → Pass (coupon)
// $built->google()    → OfferClass + OfferObject
// Then normalize with Symfony Serializer + this library’s normalizers.
```

### Example B — flight boarding (both stores)

```php
use Jolicode\WalletKit\Builder\WalletPass;
use Jolicode\WalletKit\Builder\WalletPlatformContext;
use Jolicode\WalletKit\Pass\Android\Model\Flight\AirportInfo;
use Jolicode\WalletKit\Pass\Android\Model\Flight\FlightCarrier;
use Jolicode\WalletKit\Pass\Android\Model\Flight\FlightHeader;
use Jolicode\WalletKit\Pass\Android\Model\Flight\ReservationInfo;
use Jolicode\WalletKit\Pass\Android\Model\Shared\ReviewStatusEnum;
use Jolicode\WalletKit\Pass\Android\Model\Shared\StateEnum;

$flightContext = new WalletPlatformContext(
    appleTeamIdentifier: 'ABCDE12345',
    applePassTypeIdentifier: 'pass.com.example.boarding',
    appleSerialNumber: 'BP-8844',
    appleOrganizationName: 'Example Airways',
    appleDescription: 'SFO → LAX',
    googleClassId: '3388000000012345.example_flight_class',
    googleObjectId: '3388000000012345.example_flight_object',
    defaultGoogleReviewStatus: ReviewStatusEnum::Approved,
    defaultGoogleObjectState: StateEnum::Active,
);

$built = WalletPass::flight(
    $flightContext,
    passengerName: 'Taylor Lee',
    reservationInfo: new ReservationInfo(confirmationCode: 'ABC123'),
    flightHeader: new FlightHeader(
        carrier: new FlightCarrier(carrierIataCode: 'ZZ'),
        flightNumber: '101',
    ),
    origin: new AirportInfo(airportIataCode: 'SFO'),
    destination: new AirportInfo(airportIataCode: 'LAX'),
)
    ->withGrouping('trip-sfo-lax-2026', 0)
    ->build();

// $built->apple()  → boardingPass + PKTransitTypeAir
// $built->google() → FlightClass + FlightObject
```

### 🍏 Apple Wallet

Apple’s model maps to a **single** tree: either use the **builder** above or build a `Pass` manually (see `src/Pass/Apple/`) and normalize it to the structure that becomes **`pass.json`** inside a pass package. Images, manifest, and cryptographic signing are still your responsibility.

### 🤖 Google Wallet

Google’s API splits each pass type into **two** resources: a **class** (shared template) and an **object** (one per holder). The object references the class through **`classId`**. This library exposes both sides under `src/Pass/Android/` for: EventTicket, Flight, Generic, GiftCard, Loyalty, Offer, and Transit. The **builder** returns that pair from `BuiltWalletPass::google()`.

## Install

```bash
composer require jolicode/wallet-kit
```

## Package layout

- `Jolicode\WalletKit\Pass\Apple` — Apple Wallet `pass.json` payloads
- `Jolicode\WalletKit\Pass\Android` — Google Wallet class and object payloads
- `Jolicode\WalletKit\Builder` — Fluent dual-platform builders (`WalletPass`, …)

## Documentation

- **[Builder examples (all verticals)](docs/builder-examples.md)**

## License

View the [LICENSE](LICENSE) file attached to this project.

<br>
<div align="center">
<a href="https://jolicode.com/"><img src="https://jolicode.com/media/original/oss/footer-github.png?v3" alt="JoliCode is sponsoring this project"></a>
</div>
