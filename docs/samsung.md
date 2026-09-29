# Wallet Kit -- Samsung Wallet

> Need to provision a Samsung Wallet Partners account, service ID, or signing key first? See the [Samsung setup guide](setup/samsung.md).

## Contents

- [Authentication](#authentication)
- [CRUD operations](#crud-operations)
- [Card state management](#card-state-management)
- [Credentials reference](#credentials-reference)
- [Full example](#full-example)
- [Without the bundle](#without-the-bundle)

---

## Authentication

Samsung Wallet uses per-request RS256 JWS "Authorization Tokens" signed with your partner private key, as documented in the [Samsung security guide](https://developer.samsung.com/wallet/securityauthentication/restapiauthorizationtoken.html). [`SamsungRequestAuthenticator`](../src/Api/Auth/SamsungRequestAuthenticator.php) builds one token per API call, bound to the HTTP method and path.

```php
use Jolicode\WalletKit\Api\Auth\SamsungRequestAuthenticator;
use Jolicode\WalletKit\Api\Credentials\SamsungCredentials;
use Jolicode\WalletKit\Api\Samsung\SamsungRegionEnum;

$credentials = new SamsungCredentials(
    partnerId: 'your-partner-id',
    privateKeyPath: '/path/to/samsung-private-key.pem',
    certificateId: 'YMtt',                // certificate identifier from the Samsung Partner site
    region: SamsungRegionEnum::EU,        // us | eu | kr
);

$authenticator = new SamsungRequestAuthenticator($credentials);

// Per-request binding (the token is minted fresh for every API call by the client):
$token = $authenticator->createAuthorizationToken('POST', '/partner/v1/card/template');
```

The JWS header carries `cty: "AUTH"`, `ver: 3`, your `certificateId`, `partnerId` and a `utc` timestamp; the payload carries `API: {method, path}`. There is nothing to cache — the token binds to exactly one request. The `openssl` PHP extension is required.

---

## CRUD operations

[`SamsungWalletClient`](../src/Api/Samsung/SamsungWalletClient.php) wraps the Samsung Partner API (`v2.1`). All methods return a [`SamsungApiResponse`](../src/Api/Samsung/SamsungApiResponse.php).

### Create a card

```php
$response = $client->createCard($card);
```

### Get a card

```php
$response = $client->getCard('card-id-123');
```

### Update a card

```php
$response = $client->updateCard($card, 'card-id-123');
```

Samsung implicitly pushes updated data to the user's device when you call `updateCard()`.

### Card state

Card state transitions are driven through card data updates (the normalized
`Card` includes the state) — send them via `createCard()` / `updateCard()`.
Consumer state changes then propagate by pushing new card data to devices:

### Push card update

```php
$response = $client->pushCardUpdate('card-id-123', 'event-1', 'CARD_UPDATED');
```

Use `pushCardUpdate()` to explicitly re-push a card to the user's device without changing any data. This is useful when you need to trigger a refresh on the device after an external change.

### Response handling

```php
if ($response->isSuccessful()) {
    $data = $response->getData(); // array<string, mixed>
} else {
    $statusCode = $response->getStatusCode();
}
```

A `429` status code throws a [`RateLimitException`](../src/Exception/Api/RateLimitException.php) with the `Retry-After` header value when available. Transport failures throw [`HttpRequestException`](../src/Exception/Api/HttpRequestException.php).

---

## Card state management

Samsung cards have a lifecycle driven by card data. Use `updateCard()` for data/state changes (Samsung delivers updates to devices implicitly) and `pushCardUpdate()` to force an immediate refresh.

| Scenario | Method | Push behavior |
|----------|--------|---------------|
| Change card data/state (title, barcode, ...) | `updateCard()` | Implicit push |
| Force refresh on device | `pushCardUpdate(cardId, eventId, type)` | Explicit pull notification |

---

## Credentials reference

[`SamsungCredentials`](../src/Api/Credentials/SamsungCredentials.php) holds the partner configuration.

| Property | Type | Required | Description |
|----------|------|----------|-------------|
| `partnerId` | `string` | yes | Samsung partner ID |
| `privateKeyPath` | `string` | yes | Path to the RSA private key (PEM) |
| `certificateId` | `string` | yes | Certificate identifier from the Samsung Partner site (required in every Authorization JWS) |
| `publicKeyPath` | `?string` | no | Samsung public certificate (PEM) — verifies inbound notification signatures |

Bundle configuration (`config/packages/wallet_kit.yaml`):

```yaml
wallet_kit:
    samsung:
        partnerId: '%env(SAMSUNG_PARTNER_ID)%'
        privateKeyPath: '%env(SAMSUNG_PRIVATE_KEY_PATH)%'
        certificateId: '%env(SAMSUNG_CERTIFICATE_ID)%'
        publicKeyPath: ~           # optional: verify inbound notification JWS
        apiBatchSize: 100          # default
        apiBatchInterval: 30       # default (seconds)
```

---

## Full example

Build a coupon card with the builder, create it via the API, update its state, and push.

```php
<?php

declare(strict_types=1);

use Jolicode\WalletKit\Api\Auth\SamsungRequestAuthenticator;
use Jolicode\WalletKit\Api\Credentials\SamsungCredentials;
use Jolicode\WalletKit\Api\Samsung\SamsungRegionEnum;
use Jolicode\WalletKit\Api\Samsung\SamsungWalletClient;
use Jolicode\WalletKit\Builder\WalletPass;
use Jolicode\WalletKit\Builder\WalletPlatformContext;
use Jolicode\WalletKit\Pass\Android\Model\Offer\RedemptionChannelEnum;
use Jolicode\WalletKit\Pass\Apple\Model\Barcode;
use Jolicode\WalletKit\Pass\Apple\Model\BarcodeFormatEnum;

// 1. Build a Samsung card using the builder
$context = (new WalletPlatformContext())->withSamsung(
    refId: 'coupon-001',
    appLinkLogo: 'https://example.com/logo.png',
    appLinkName: 'Example Shop',
    appLinkData: 'https://example.com',
);

$built = WalletPass::offer(
    $context,
    title: '15% off your next purchase',
    provider: 'Example Shop',
    redemptionChannel: RedemptionChannelEnum::INSTORE,
)->addAppleBarcode(new Barcode(
    altText: 'Coupon',
    format: BarcodeFormatEnum::QR,
    message: 'SAVE15',
    messageEncoding: 'utf-8',
))->build();

$card = $built->samsung();

// 2. Set up the API client
$credentials = new SamsungCredentials(
    partnerId: 'your-partner-id',
    privateKeyPath: '/path/to/private-key.pem',
    certificateId: 'YMtt',                      // Samsung Partner site certificate identifier
    publicKeyPath: null,                        // optional: verify inbound notifications
    region: SamsungRegionEnum::EU,
);

$authenticator = new SamsungRequestAuthenticator($credentials);

$client = new SamsungWalletClient(
    httpClient: $httpClient,       // Symfony HttpClientInterface
    normalizer: $normalizer,       // Symfony NormalizerInterface
    authenticator: $authenticator,
    credentials: $credentials,
);

// 3. Create the card
$response = $client->createCard($card);

if (!$response->isSuccessful()) {
    throw new \RuntimeException('Failed to create card: ' . $response->getStatusCode());
}

$cardId = $response->getData()['cardId'];

// 4. Push fresh card data to registered devices
$client->pushCardUpdate($cardId, 'event-' . $cardId, 'CARD_UPDATED');
```

---

## Without the bundle

When not using the Symfony bundle, wire the services manually:

```php
<?php

declare(strict_types=1);

use Jolicode\WalletKit\Api\Auth\SamsungRequestAuthenticator;
use Jolicode\WalletKit\Api\Credentials\SamsungCredentials;
use Jolicode\WalletKit\Api\Samsung\SamsungRegionEnum;
use Jolicode\WalletKit\Api\Samsung\SamsungWalletClient;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

$credentials = new SamsungCredentials(
    partnerId: 'your-partner-id',
    privateKeyPath: '/path/to/private-key.pem',
    certificateId: 'YMtt',
);

$authenticator = new SamsungRequestAuthenticator($credentials);

$serializer = new Serializer(
    [new ObjectNormalizer()],
    [new JsonEncoder()],
);

$client = new SamsungWalletClient(
    httpClient: HttpClient::create(),
    normalizer: $serializer,
    authenticator: $authenticator,
    credentials: $credentials,
);
```

For production use, build the Serializer with [`WalletSerializerFactory::create()`](../src/Builder/WalletSerializerFactory.php) — it registers every normalizer this package ships (including the Samsung ones like [`SamsungImage`](../src/Pass/Samsung/Model/Shared/SamsungImage.php) and [`SamsungBarcode`](../src/Pass/Samsung/Model/Shared/SamsungBarcode.php)).
