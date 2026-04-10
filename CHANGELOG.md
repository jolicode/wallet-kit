# Changelog

## Unreleased

### Added

- **Samsung Wallet support** — 8 card types (BoardingPass, EventTicket, Coupon, GiftCard, Loyalty, Generic, DigitalId, PayAsYouGo) with unified JSON envelope model, normalizers, and builder integration for all 7 cross-platform verticals.
- **`Color` value object** — unified color handling with `Color::fromRgb()`, `Color::fromHex()`, `Color::fromRgbString()` factory methods and `->rgb()` / `->hex()` output, replacing raw string color values across all platforms.

## [1.0.0](https://github.com/jolicode/wallet-kit/releases/tag/v1.0.0)

### Added

- **Apple Wallet support** — typed PHP models for `pass.json` payloads covering all Apple pass types (boarding pass, coupon, event ticket, generic, store card) with full Symfony Serializer normalizers.
- **Google Wallet support** — class and object models for all Google Wallet verticals (EventTicket, Flight, Generic, GiftCard, Loyalty, Offer, Transit) with normalizers.
- **Fluent builder API** — `WalletPass` factory with 7 verticals (`generic`, `offer`, `loyalty`, `eventTicket`, `flight`, `transit`, `giftCard`), `WalletPlatformContext` with `->withApple()`, `->withGoogle()` for single or multi-platform builds.
- **Spec check tooling** — Castor tasks to verify Apple and Google models against their respective baselines (`spec:check:apple`, `spec:check:google`) with baseline refresh commands.
- **Google Wallet API baseline tracking** — discovery document revision tracking (current: `20260409`) with automated drift detection.
