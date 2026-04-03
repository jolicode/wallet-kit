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

### 🍏 Apple Wallet

Apple’s model maps to a **single** tree: build a `Pass` (see `src/Pass/Apple/`) and normalize it to the structure that becomes **`pass.json`** inside a pass package. Images, manifest, and cryptographic signing are still your responsibility.

### 🤖 Google Wallet

Google’s API splits each pass type into **two** resources: a **class** (shared template) and an **object** (one per holder). The object references the class through **`classId`**. This library exposes both sides under `src/Pass/Android/` for: EventTicket, Flight, Generic, GiftCard, Loyalty, Offer, and Transit.

## Install

```bash
composer require jolicode/wallet-kit
```

## Package layout

- `Jolicode\WalletKit\Pass\Apple` — Apple Wallet `pass.json` payloads
- `Jolicode\WalletKit\Pass\Android` — Google Wallet class and object payloads

## Documentation

@TODO

## License

View the [LICENSE](LICENSE) file attached to this project.

<br>
<div align="center">
<a href="https://jolicode.com/"><img src="https://jolicode.com/media/original/oss/footer-github.png?v3" alt="JoliCode is sponsoring this project"></a>
</div>
