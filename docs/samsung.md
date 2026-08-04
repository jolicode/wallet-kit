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

Samsung Wallet uses RS256 JWTs signed with your partner private key. [`SamsungJwtAuthenticator`](../src/Api/Auth/SamsungJwtAuthenticator.php) handles token creation and caching.

```php
use Jolicode\WalletKit\Api\Auth\SamsungJwtAuthenticator;
use Jolicode\WalletKit\Api\Credentials\SamsungCredentials;

$credentials = new SamsungCredentials(
    partnerId: 'your-partner-id',
    privateKeyPath: '/path/to/samsung-private-key.pem',
);

$authenticator = new SamsungJwtAuthenticator($credentials);

$token = $authenticator->getToken();
$token->getAccessToken(); // RS256 JWT string
$token->isExpired();      // false (cached ~59 min)
```

The JWT includes `iss` (partner ID), `iat`, and `exp` (1 hour) claims. Tokens are cached in memory and reused until 1 minute before expiry. The `openssl` PHP extension is required.

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

### Update card state

```php
$response = $client->updateCardState('card-id-123', 'USED');
```

### Push card update

```php
$response = $client->pushCardUpdate('card-id-123');
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

Samsung cards have a lifecycle driven by state transitions. Use `updateCard()` for data changes and `updateCardState()` for state-only transitions.

| Scenario | Method | Push behavior |
|----------|--------|---------------|
| Change card data (title, barcode, ...) | `updateCard()` | Implicit push |
| Transition state (e.g. mark as used) | `updateCardState()` | No implicit push |
| Force refresh on device | `pushCardUpdate()` | Explicit push |

When updating card data via `updateCard()`, Samsung automatically delivers the update to the user's device. After a state-only change via `updateCardState()`, call `pushCardUpdate()` if you need the device to reflect the change immediately.

---

## Credentials reference

[`SamsungCredentials`](../src/Api/Credentials/SamsungCredentials.php) holds the partner configuration.

| Property | Type | Required | Description |
|----------|------|----------|-------------|
| `partnerId` | `string` | yes | Samsung partner ID |
| `privateKeyPath` | `string` | yes | Path to the RSA private key (PEM) |
| `serviceId` | `?string` | no | Optional service identifier |

Bundle configuration (`config/packages/wallet_kit.yaml`):

```yaml
wallet_kit:
    samsung:
        partnerId: '%env(SAMSUNG_PARTNER_ID)%'
        privateKeyPath: '%env(SAMSUNG_PRIVATE_KEY_PATH)%'
        serviceId: ~               # optional
        apiBatchSize: 100          # default
        apiBatchInterval: 30       # default (seconds)
```

---

## Full example

Build a coupon card with the builder, create it via the API, update its state, and push.

```php
<?php

declare(strict_types=1);

use Jolicode\WalletKit\Api\Auth\SamsungJwtAuthenticator;
use Jolicode\WalletKit\Api\Credentials\SamsungCredentials;
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
);

$authenticator = new SamsungJwtAuthenticator($credentials);

$client = new SamsungWalletClient(
    httpClient: $httpClient,       // Symfony HttpClientInterface
    normalizer: $normalizer,       // Symfony NormalizerInterface
    authenticator: $authenticator,
);

// 3. Create the card
$response = $client->createCard($card);

if (!$response->isSuccessful()) {
    throw new \RuntimeException('Failed to create card: ' . $response->getStatusCode());
}

$cardId = $response->getData()['cardId'];

// 4. Later: mark the card as used
$client->updateCardState($cardId, 'USED');

// 5. Push the state change to the device
$client->pushCardUpdate($cardId);
```

---

## Without the bundle

When not using the Symfony bundle, wire the services manually:

```php
<?php

declare(strict_types=1);

use Jolicode\WalletKit\Api\Auth\SamsungJwtAuthenticator;
use Jolicode\WalletKit\Api\Credentials\SamsungCredentials;
use Jolicode\WalletKit\Api\Samsung\SamsungWalletClient;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

$credentials = new SamsungCredentials(
    partnerId: 'your-partner-id',
    privateKeyPath: '/path/to/private-key.pem',
    serviceId: null,
);

$authenticator = new SamsungJwtAuthenticator($credentials);

$serializer = new Serializer(
    [new ObjectNormalizer()],
    [new JsonEncoder()],
);

$client = new SamsungWalletClient(
    httpClient: HttpClient::create(),
    normalizer: $serializer,
    authenticator: $authenticator,
);
```

For production use, register the normalizers from this package (see [`tests/Builder/BuilderTestSerializerFactory.php`](../tests/Builder/BuilderTestSerializerFactory.php) for the full list) to ensure Samsung models like [`SamsungImage`](../src/Pass/Samsung/Model/Shared/SamsungImage.php) and [`SamsungBarcode`](../src/Pass/Samsung/Model/Shared/SamsungBarcode.php) are serialized correctly.
