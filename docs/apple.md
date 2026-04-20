# Apple Wallet

This page covers Apple-specific APIs: `.pkpass` packaging, APNS push notifications, and credentials setup. For the cross-platform builder that produces Apple `Pass` models, see [builder-examples.md](builder-examples.md).

## Contents

- [`.pkpass` packaging](#pkpass-packaging)
- [Push notifications](#push-notifications)
- [Credentials reference](#credentials-reference)
- [Full example](#full-example)
- [Without the bundle](#without-the-bundle)

---

## `.pkpass` packaging

[`ApplePassPackager`](../src/Api/Apple/ApplePassPackager.php) takes a `Pass` model, signs it with your P12 certificate, and returns raw `.pkpass` bytes (a ZIP archive containing `pass.json`, images, localization files, `manifest.json`, and `signature`).

### Prerequisites

- PHP extensions: `openssl`, `zip`
- A **P12 certificate** exported from your Apple Developer account (Certificates, Identifiers & Profiles > Pass Type ID certificate)
- The WWDR intermediate CA is bundled (`AppleWWDRCAG4.cer`); override via `AppleCredentials::$wwdrCertificatePath` if needed

### Basic usage

```php
use Jolicode\WalletKit\Api\Apple\ApplePassPackager;
use Jolicode\WalletKit\Api\Credentials\AppleCredentials;

$credentials = new AppleCredentials(
    certificatePath: '/path/to/pass-certificate.p12',
    certificatePassword: 'p12-password',
);

// $serializer is a Symfony Serializer instance (see "Without the bundle" below)
$packager = new ApplePassPackager($serializer, $credentials);

// $pass is a \Jolicode\WalletKit\Pass\Apple\Model\Pass built via the builder
$pkpass = $packager->package($pass, [
    'icon.png'    => '/absolute/path/to/icon.png',
    'icon@2x.png' => '/absolute/path/to/icon@2x.png',
    'logo.png'    => 'https://example.com/assets/logo.png',
]);
```

The `$images` array maps filenames inside the `.pkpass` to local paths or URLs. The packager reads each file with `file_get_contents`, so both absolute paths and `https://` URLs work.

### Localized resources

The third argument accepts an `array<string, array<string, string>>` of locale to key-value pairs. The packager generates `{locale}.lproj/pass.strings` files automatically.

```php
$pkpass = $packager->package($pass, $images, [
    'fr' => [
        'gate_label' => 'Porte',
        'boarding_time' => 'Heure d\'embarquement',
    ],
    'de' => [
        'gate_label' => 'Gate',
        'boarding_time' => 'Boarding-Zeit',
    ],
]);
```

For localized images (e.g. `fr.lproj/logo.png`), include the `.lproj/` prefix in the images array key:

```php
$images = [
    'icon.png'            => '/path/to/icon.png',
    'logo.png'            => '/path/to/logo-en.png',
    'fr.lproj/logo.png'   => '/path/to/logo-fr.png',
];
```

---

## Push notifications

[`ApplePushNotifier`](../src/Api/Apple/ApplePushNotifier.php) sends empty background pushes via APNs HTTP/2 to tell Apple Wallet to re-fetch the pass from your web service.

### Prerequisites

- A **P8 key** (Apple Developer > Keys > Apple Push Notifications service) with its key ID and your team ID
- An `HttpClientInterface` implementation (Symfony HttpClient)

### Setup

```php
use Jolicode\WalletKit\Api\Auth\AppleApnsJwtProvider;
use Jolicode\WalletKit\Api\Apple\ApplePushNotifier;
use Jolicode\WalletKit\Api\Credentials\AppleCredentials;

$credentials = new AppleCredentials(
    certificatePath: '/path/to/pass-certificate.p12',
    certificatePassword: 'p12-password',
    apnsKeyPath: '/path/to/AuthKey_XXXXXXXXXX.p8',
    apnsKeyId: 'XXXXXXXXXX',
    apnsTeamId: 'YOUR_TEAM_ID',
);

$jwtProvider = new AppleApnsJwtProvider($credentials);

// $httpClient is a Symfony HttpClientInterface
$notifier = new ApplePushNotifier($httpClient, $jwtProvider, sandbox: false);
```

The JWT provider caches tokens for ~50 minutes (Apple tokens expire after 1 hour).

### Single push

```php
$response = $notifier->sendUpdateNotification($pushToken, 'pass.com.example.app');

if ($response->isSuccessful()) {
    // Apple will contact your web service for the updated pass
}
```

### Batch push

Requests are fired concurrently (HTTP/2 multiplexing via Symfony HttpClient).

```php
$responses = $notifier->sendBatchUpdateNotifications($pushTokens, 'pass.com.example.app');

foreach ($responses as $response) {
    if ($response->isDeviceTokenInactive()) {
        // 410 Gone -- remove this token from your database
        $tokenToRemove = $response->getPushToken();
    }

    if ($response->isRateLimited()) {
        // 429 Too Many Requests -- back off
    }

    if (!$response->isSuccessful()) {
        $reason = $response->getErrorReason(); // e.g. "BadDeviceToken"
        $apnsId = $response->getApnsId();
    }
}
```

### `ApnsPushResponse` methods

See [`ApnsPushResponse`](../src/Api/Apple/ApnsPushResponse.php).

| Method                     | Returns  | Description                              |
|----------------------------|----------|------------------------------------------|
| `isSuccessful()`           | `bool`   | `true` when status is 200               |
| `isDeviceTokenInactive()`  | `bool`   | `true` when status is 410 (Gone)        |
| `isRateLimited()`          | `bool`   | `true` when status is 429               |
| `getPushToken()`           | `string` | The device push token from the request   |
| `getStatusCode()`          | `int`    | Raw HTTP status code                     |
| `getErrorReason()`         | `?string`| APNs error reason (e.g. `BadDeviceToken`)|
| `getApnsId()`              | `?string`| APNs notification UUID                   |

---

## Credentials reference

See [`AppleCredentials`](../src/Api/Credentials/AppleCredentials.php).

```php
use Jolicode\WalletKit\Api\Credentials\AppleCredentials;

$credentials = new AppleCredentials(
    // Required -- P12 certificate for .pkpass signing
    certificatePath: '/path/to/pass-certificate.p12',
    certificatePassword: 'p12-password',

    // Optional -- WWDR intermediate CA (null uses bundled AppleWWDRCAG4.cer)
    wwdrCertificatePath: null,

    // Optional -- P8 key for APNS push notifications
    apnsKeyPath: '/path/to/AuthKey_XXXXXXXXXX.p8',
    apnsKeyId: 'XXXXXXXXXX',
    apnsTeamId: 'YOUR_TEAM_ID',

    // Optional -- used by the builder context, not directly by packager/notifier
    teamIdentifier: 'YOUR_TEAM_ID',
    passTypeIdentifier: 'pass.com.example.app',
);
```

---

## Full example

Build a loyalty pass, package it as `.pkpass`, serve it as an HTTP response, then push a refresh to registered devices.

```php
<?php

declare(strict_types=1);

use Jolicode\WalletKit\Api\Apple\ApplePassPackager;
use Jolicode\WalletKit\Api\Apple\ApplePushNotifier;
use Jolicode\WalletKit\Api\Auth\AppleApnsJwtProvider;
use Jolicode\WalletKit\Api\Credentials\AppleCredentials;
use Jolicode\WalletKit\Builder\WalletPass;
use Jolicode\WalletKit\Builder\WalletPlatformContext;

// 1. Credentials
$credentials = new AppleCredentials(
    certificatePath: '/path/to/pass-certificate.p12',
    certificatePassword: 'p12-password',
    apnsKeyPath: '/path/to/AuthKey_XXXXXXXXXX.p8',
    apnsKeyId: 'XXXXXXXXXX',
    apnsTeamId: 'YOUR_TEAM_ID',
    teamIdentifier: 'YOUR_TEAM_ID',
    passTypeIdentifier: 'pass.com.example.app',
);

// 2. Build the pass model
$context = (new WalletPlatformContext())->withApple(
    teamIdentifier: $credentials->teamIdentifier,
    passTypeIdentifier: $credentials->passTypeIdentifier,
    serialNumber: 'LYL-001',
    organizationName: 'Example Coffee',
    description: 'Loyalty card',
);

$built = WalletPass::loyalty($context, programName: 'Gold Rewards')
    ->withAccount(accountName: 'Jane Doe', accountId: 'GOLD-42')
    ->withAppleWebService(
        url: 'https://api.example.com/v1/passes/',
        authenticationToken: 'shared-secret-token',
    )
    ->build();

$pass = $built->apple();

// 3. Package as .pkpass
$packager = new ApplePassPackager($serializer, $credentials);
$pkpassData = $packager->package($pass, [
    'icon.png'    => '/path/to/icon.png',
    'icon@2x.png' => '/path/to/icon@2x.png',
    'logo.png'    => '/path/to/logo.png',
]);

// 4. Serve as HTTP response (Symfony)
return new \Symfony\Component\HttpFoundation\Response($pkpassData, 200, [
    'Content-Type' => 'application/vnd.apple.pkpass',
    'Content-Disposition' => 'attachment; filename="loyalty.pkpass"',
]);

// 5. Later, push a refresh to registered devices
$jwtProvider = new AppleApnsJwtProvider($credentials);
$notifier = new ApplePushNotifier($httpClient, $jwtProvider);

$responses = $notifier->sendBatchUpdateNotifications(
    $pushTokens,    // tokens from your web service registration endpoint
    'pass.com.example.app',
);

foreach ($responses as $response) {
    if ($response->isDeviceTokenInactive()) {
        // Remove token from database
    }
}
```

---

## Without the bundle

If you are not using the Symfony bundle, you need to build the `Serializer` manually. The test factory at [`tests/Builder/BuilderTestSerializerFactory.php`](../tests/Builder/BuilderTestSerializerFactory.php) shows the full list of normalizers.

For Apple-only usage, you need at minimum the Apple normalizers:

```php
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
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;

$serializer = new Serializer([
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
], [
    new JsonEncoder(),
]);
```

For the HttpClient (push notifications), any PSR-18 or Symfony HttpClient implementation works:

```php
use Symfony\Component\HttpClient\HttpClient;

$httpClient = HttpClient::create();
```
