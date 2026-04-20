# AGENTS.md

Guidance for AI agents in this repo.

## Project

wallet-kit is a PHP 8.3+ library for Apple Wallet, Google Wallet, and Samsung Wallet. It covers the full lifecycle: fluent builder API for modeling payloads (via Symfony Serializer), `.pkpass` signing and packaging, API clients for Google and Samsung, Apple push notifications, and an optional Symfony Bundle for full integration (DI, routes, controllers, throttled bulk operations).

## Commands

```bash
vendor/bin/phpunit                          # tests (PHPUnit 12)
vendor/bin/phpunit --filter=Foo[::test]     # single test

castor qa:phpstan                           # PHPStan level 5
castor qa:cs:check | qa:cs:fix              # PHP-CS-Fixer

castor spec:check:{google|apple|samsung}    # drift vs upstream spec
castor spec:diff:google [--properties]      # detailed Google diff
castor spec:baseline:{google|apple|samsung} # refresh baseline
```

CI: cs-check, spec-check, phpstan, tests (PHP 8.3/8.4/8.5).

## Agent workflows

Canonical sources in `.agents/`. `.claude/` and `.cursor/` entries are symlinks — edit the source, both tools see it.

```
.agents/
  agents/      # subagent / command prompts
    spec-drift-investigator.md   — report model/spec drift (read-only)
    wallet-payload-reviewer.md   — review builder/Pass serialization changes
  skills/      # slash-command workflows
    refresh-wallet-spec.md       — detect → diff → decide → patch → baseline → verify
  scripts/     # shared hook scripts
    guard-protected-paths.sh
```

Protected paths: `tools/spec/*.json`, `**/.env.local`. Hard block via `.agents/scripts/guard-protected-paths.sh` (Claude Code PreToolUse hook); rule via `.cursor/rules/protected-files.mdc` (Cursor).

## Architecture

### Builder

```
WalletPass::{vertical}(WalletPlatformContext, ...)
  → ConcreteBuilder (AbstractWalletBuilder + CommonWalletBuilderTrait)
  → .with*() / .add*() → .build() → BuiltWalletPass
    → .apple()   → Pass
    → .google()  → GoogleWalletPair (vertical + issuerClass + passObject)
    → .samsung() → Card
```

Verticals: Generic, Offer, Loyalty, EventTicket, Flight, Transit, GiftCard — each in `src/Builder/{Vertical}/`.

`WalletPlatformContext`: immutable, built via `->withApple|withGoogle|withSamsung`. Unconfigured platforms throw typed exceptions.

### Namespaces

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
| `Api\Credentials\` | Credential value objects (AppleCredentials, GoogleCredentials, SamsungCredentials) |
| `Api\Auth\` | TokenInterface, CachedToken, OAuth2/JWT authenticators (Google, Samsung, Apple APNS) |
| `Api\Apple\` | ApplePassPackager (.pkpass signing+ZIP), ApplePushNotifier (APNs HTTP/2), ApnsPushResponse |
| `Api\Google\` | GoogleWalletClient (REST CRUD), GoogleSaveLinkGenerator (offline save links), GoogleApiResponse |
| `Api\Samsung\` | SamsungWalletClient (Partner API CRUD+push), SamsungApiResponse |
| `Api\` | IssuanceHelper (Add to Wallet URLs for all 3 platforms) |
| `Bundle\` | WalletKitBundle, WalletContextFactory, DI configuration |
| `Bundle\Controller\` | Apple Web Service, Google/Samsung callback controllers |
| `Bundle\Repository\` | PassRegistration and PendingOperation repository interfaces + Doctrine implementations |
| `Bundle\Entity\` | PassRegistration, PendingOperation Doctrine entities |
| `Bundle\Messenger\` | ProcessPendingOperationsMessage + Handler (Symfony Messenger) |
| `Bundle\Processor\` | PendingOperationProcessorInterface + Apple/Google/Samsung processors |
| `Bundle\Push\` | ThrottledPushDispatcher (Apple) |
| `Bundle\Google\` | ThrottledGoogleDispatcher, GoogleCallbackHandlerInterface |
| `Bundle\Samsung\` | ThrottledSamsungDispatcher, SamsungCallbackHandlerInterface |
| `Common\` | Shared value objects (Color) |
| `Exception\` | Typed exceptions implementing WalletKitException |
| `Exception\Api\` | API/packaging exceptions (AuthenticationException, HttpRequestException, ApiResponseException, RateLimitException, PackagingException, MissingExtensionException) |

### Serialization

All JSON via Symfony Serializer normalizers (100+). Tests wire the stack via `BuilderTestSerializerFactory` in `tests/Builder/`.

### Platform shapes

- **Apple Wallet:** Single `Pass` object → one `pass.json`. Packaged into `.pkpass` via `ApplePassPackager`. Push updates via APNs HTTP/2.
- **Google Wallet:** Class + Object pairs per vertical (e.g., `EventTicketClass` + `EventTicketObject`), wrapped in `GoogleWalletPair`. CRUD via `GoogleWalletClient`. Save links via `GoogleSaveLinkGenerator`.
- **Samsung Wallet:** Unified `Card` envelope with type-specific attributes, 8 card types (7 cross-platform + DigitalId and PayAsYouGo are Samsung-only). CRUD + push via `SamsungWalletClient`.

### API Layer

- **No new required dependencies** — all API features are optional (ext-openssl, ext-zip, symfony/http-client in `suggest`)
- **Auth**: each platform has its own authenticator with in-memory token caching via `CachedToken`
- **Exceptions**: all implement `WalletKitException`. `RateLimitException` extends `ApiResponseException` for 429 with `retryAfterSeconds`
- **Symfony Bundle**: no autowiring — all services explicitly defined in PHP config files, conditionally loaded per platform

### Conventions

- PHPStan level 5 with extensive `@phpstan-type` shape annotations for validation
- Enums used throughout (CardTypeEnum, PassTypeEnum, GoogleVerticalEnum, ReviewStatusEnum, WalletPlatformEnum, etc.)
- `mutateApple()` and `mutateSamsung()` callbacks allow post-build platform-specific customization
- Color value object supports `rgb()`, `hex()`, and `googleColor()` output formats
- Use `\array_key_exists()` instead of `isset()` or `empty()` in all PHP code
- All concrete classes are `final class`

### Documentation

- `docs/apple.md` — `.pkpass` packaging, push notifications, standalone usage
- `docs/google.md` — API client, save links, standalone usage
- `docs/samsung.md` — API client, card state management, standalone usage
- `docs/bundle.md` — Symfony Bundle: config, DI, Apple Web Service, callbacks, throttled operations
- `docs/builder-examples.md` — Builder cookbook with examples for all 7 verticals
