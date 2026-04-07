<h1 align="center">
  <a href="https://github.com/jolicode/wallet-kit"><img src="https://jolicode.com/media/original/oss/headers/wallet-kit.png" alt="wallet-kit"></a>
  <br />
  WalletKit
  <br />
  <sub><em><h6>One fluent API to model Apple Wallet and Google Wallet payloads in typed PHP.</h6></em></sub>
</h1>

<div align="center">

[![PHP Version Require](http://poser.pugx.org/jolicode/wallet-kit/require/php)](https://packagist.org/packages/jolicode/wallet-kit)
[![Monthly Downloads](http://poser.pugx.org/jolicode/wallet-kit/d/monthly)](https://packagist.org/packages/jolicode/wallet-kit)

</div>

## Overview

Wallet Kit helps you build the **JSON payloads** wallet platforms expect. It focuses on **modeling and normalization** (via Symfony Serializer): it does **not** sign Apple passes, bundle `.pkpass` files, or call Google Wallet APIs.

- **PHP** 8.3+
- **symfony/serializer** ^7.4 || ^8.0

## 🛠️ Builder

The **`Jolicode\WalletKit\Builder`** namespace provides a fluent API centered on [`WalletPass`](src/Builder/WalletPass.php). Whether you need to build passes for **Apple, Google, or both platforms simultaneously**, use [`WalletPlatformContext::both(...)`](src/Builder/WalletPlatformContext.php) and then call `build()` to obtain a [`BuiltWalletPass`](src/Builder/BuiltWalletPass.php) (`apple()`, `google()`). You can then normalize these models using Symfony Serializer along with this package’s normalizers.

**Cookbook** (single-store `appleOnly` / `googleOnly`, every vertical, shared options, exceptions): [docs/builder-examples.md](docs/builder-examples.md).

### Example — dual platform

```php
$context = WalletPlatformContext::both(
    appleTeamIdentifier: 'ABCDE12345',
    applePassTypeIdentifier: 'pass.com.example.coupon',
    appleSerialNumber: 'COUPON-001',
    appleOrganizationName: 'Example Shop',
    appleDescription: 'Spring sale coupon',
    googleClassId: '3388000000012345.example_offer_class',
    googleObjectId: '3388000000012345.example_offer_object',
    defaultGoogleReviewStatus: ReviewStatusEnum::APPROVED,
    defaultGoogleObjectState: StateEnum::ACTIVE,
);

$built = WalletPass::offer(
    $context,
    title: '15% off',
    provider: 'Example Shop',
    redemptionChannel: RedemptionChannelEnum::BOTH,
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
- `Jolicode\WalletKit\Builder` — Fluent builders (`WalletPass`, …) for Apple, Google, or both
- `Jolicode\WalletKit\Exception` — Builder context and `BuiltWalletPass` accessor exceptions

## API spec checks (with Castor)

When [Castor](https://github.com/jolicode/castor) is available, you can verify that tracked baselines still match the **Google Wallet discovery document** and the **Apple `pass.json` phpstan shapes** in this repo:

| Command | Purpose |
| --- | --- |
| `castor spec:check:google` | Fetches the live Wallet Objects discovery and compares its `revision` to [`tools/spec/google-wallet-baseline.json`](tools/spec/google-wallet-baseline.json). |
| `castor spec:baseline:google` | After you update Android models for a new discovery revision, refreshes that JSON baseline. |
| `castor spec:check:apple` | Regenerates a key list from `src/Pass/Apple/Model` `@phpstan-type` array shapes and diffs it against [`tools/spec/apple-pass-keyset.json`](tools/spec/apple-pass-keyset.json). |
| `castor spec:baseline:apple` | Rewrites `apple-pass-keyset.json` from the current phpstan definitions (run after intentional model changes). |

Scripts live under [`tools/spec/`](tools/spec/) and are also invoked by CI (`spec-check` job).

## Next steps

The library today stops at **typed payloads and normalization**. Planned extensions include:

- [ ] **Apple `.pkpass` packaging** — assemble the pass bundle (images, `pass.json`, manifest), handle **localized resources** (`*.lproj`, string tables), and optionally integrate **signing** so a complete `.pkpass` can be produced from the models you already build.
- [ ] **Google Wallet API client** — HTTP integration to **create, update, and patch** classes and objects (REST), including the **service-account JWT** flow Google expects, so you can go from `BuiltWalletPass::google()` to live passes without wiring the client yourself.
- [ ] **Push notifications & in-wallet updates** — **APNs** integration to trigger **Apple Wallet** pass refreshes (silent/empty push so PassKit pulls a new `pass.json` from your web service), and **Google Wallet** support for **API-driven updates** plus **user-visible messages / notification hooks** where the platform exposes them, so pass changes actually reach holders’ devices.

Other directions that often sit next to “wallet” libraries (for later consideration):

- **Apple PassKit Web Service** — register/update HTTP endpoints, authentication token handling, and glue between your backend and the **APNs** flow above.
- **Issuance UX helpers** — stable patterns for **Add to Apple Wallet** / **Save to Google Pay** links, deep links, and optional **JWT “generic”** flows where platforms require them.
- **Operational tooling** — fixtures for integration tests, optional **CLI or Castor tasks** that mirror production steps (e.g. dry-run Google calls, local `.pkpass` inspection).

Contributions or design discussion for any of the above are welcome.

## License

View the [LICENSE](LICENSE) file attached to this project.

<br>
<div align="center">
<a href="https://jolicode.com/"><img src="https://jolicode.com/media/original/oss/footer-github.png?v3" alt="JoliCode is sponsoring this project"></a>
</div>
