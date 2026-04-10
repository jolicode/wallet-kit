# AGENTS.md

This file provides guidance to IA agents when working with code in this repository.

## Project Overview

wallet-kit is a PHP 8.3+ library that provides a fluent builder API for modeling Apple Wallet, Google Wallet, and Samsung Wallet JSON payloads. It focuses on payload normalization using Symfony Serializer — it does **not** handle signing, bundling (.pkpass), or API calls.

## Commands

```bash
# Run tests (PHPUnit 12)
vendor/bin/phpunit

# Run a single test
vendor/bin/phpunit --filter=TestClassName
vendor/bin/phpunit --filter=TestClassName::testMethodName

# Static analysis (PHPStan level 5)
castor qa:phpstan

# Code style check / fix
castor qa:cs:check
castor qa:cs:fix

# API spec drift detection
castor spec:check:google    # Google Wallet discovery doc revision
castor spec:check:apple     # Apple pass.json phpstan shapes
castor spec:check:samsung   # Samsung model keyset
castor spec:baseline:google # Refresh Google baseline
castor spec:baseline:apple  # Refresh Apple keyset
castor spec:baseline:samsung # Refresh Samsung keyset
castor spec:diff:google     # Diff live discovery enums against PHP models
castor spec:diff:google --properties # Include schema property comparison
```

CI runs 4 jobs: cs-check, spec-check, phpstan, and tests (PHP 8.3/8.4/8.5 matrix).

## Architecture

### Builder Pattern (entry point)

```
WalletPass::{vertical}(WalletPlatformContext, ...args)
  → ConcreteBuilder (extends AbstractWalletBuilder)
  → .with*() / .add*() fluent methods (via CommonWalletBuilderTrait)
  → .build() → BuiltWalletPass
    → .apple() → Pass
    → .google() → GoogleWalletPair (vertical + issuerClass + passObject)
    → .samsung() → Card
```

**7 verticals:** Generic, Offer, Loyalty, EventTicket, Flight, Transit, GiftCard — each has its own builder in `src/Builder/{Vertical}/`.

**WalletPlatformContext** is an immutable container built with `->withApple(...)`, `->withGoogle(...)`, `->withSamsung(...)`. Only configured platforms produce output; accessing unconfigured platforms throws typed exceptions.

### Namespace Layout

| Namespace | Purpose |
|-----------|---------|
| `Builder\` | WalletPass entry point, platform contexts, BuiltWalletPass |
| `Builder\Internal\` | CommonWalletState, barcode mappers, helpers |
| `Builder\{Vertical}\` | Vertical-specific builders |
| `Pass\Apple\Model\` | Apple Pass models (Pass, PassStructure, Field, Barcode, enums) |
| `Pass\Apple\Normalizer\` | Symfony Serializer normalizers for Apple |
| `Pass\Android\Model\` | Google Wallet class/object models by vertical |
| `Pass\Android\Normalizer\` | Google normalizers |
| `Pass\Samsung\Model\` | Samsung Card envelope + 8 card type attributes |
| `Pass\Samsung\Normalizer\` | Samsung normalizers |
| `Common\` | Shared value objects (Color) |
| `Exception\` | Typed exceptions implementing WalletKitException |

### Serialization

All JSON output is produced via Symfony Serializer normalizers (100+ normalizers total). Tests use `BuilderTestSerializerFactory` in `tests/Builder/` which wires up the full normalizer stack.

### Platform Differences

- **Apple Wallet:** Single `Pass` object → one `pass.json`
- **Google Wallet:** Class + Object pairs per vertical (e.g., `EventTicketClass` + `EventTicketObject`), wrapped in `GoogleWalletPair`
- **Samsung Wallet:** Unified `Card` envelope with type-specific attributes, 8 card types (7 cross-platform + DigitalId and PayAsYouGo are Samsung-only)

### Key Conventions

- PHPStan level 5 with extensive `@phpstan-type` shape annotations for validation
- Enums used throughout (CardTypeEnum, PassTypeEnum, GoogleVerticalEnum, ReviewStatusEnum, etc.)
- `mutateApple()` and `mutateSamsung()` callbacks allow post-build platform-specific customization
- Color value object supports `rgb()`, `hex()`, and `googleColor()` output formats
