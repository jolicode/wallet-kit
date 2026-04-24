# AGENTS.md

Guidance for AI agents in this repo.

## Project

wallet-kit — PHP 8.3+ library. Fluent builder for Apple/Google/Samsung Wallet JSON payloads via Symfony Serializer. No signing, no `.pkpass` bundling, no API calls.

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
|---|---|
| `Builder\` | Entry point, platform contexts, BuiltWalletPass |
| `Builder\Internal\` | CommonWalletState, barcode mappers |
| `Builder\{Vertical}\` | Vertical builders |
| `Pass\Apple\{Model,Normalizer}\` | Apple models + normalizers |
| `Pass\Android\{Model,Normalizer}\` | Google class/object models + normalizers |
| `Pass\Samsung\{Model,Normalizer}\` | Samsung Card + 8 card types |
| `Common\` | Shared VOs (Color) |
| `Exception\` | Typed, implement WalletKitException |

### Serialization

All JSON via Symfony Serializer normalizers (100+). Tests wire the stack via `BuilderTestSerializerFactory` in `tests/Builder/`.

### Platform shapes

- Apple: one `Pass` → one `pass.json`
- Google: Class + Object per vertical, wrapped in `GoogleWalletPair`
- Samsung: unified `Card` + type attributes; 8 card types (7 cross-platform + DigitalId, PayAsYouGo Samsung-only)

### Conventions

- PHPStan level 5 with `@phpstan-type` shapes
- Enums throughout (CardType, PassType, GoogleVertical, ReviewStatus, …)
- `mutateApple()` / `mutateSamsung()` for post-build tweaks
- `Color` outputs `rgb()`, `hex()`, `googleColor()`
