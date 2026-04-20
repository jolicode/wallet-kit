# Google Wallet

This page covers authentication, CRUD operations, and save-link generation for Google Wallet passes using the library's API layer.

---

## Authentication

Google Wallet uses OAuth2 with a service account. Download a service-account JSON key file from the Google Cloud console and point [`GoogleCredentials`](../src/Api/Credentials/GoogleCredentials.php) at it.

[`GoogleOAuth2Authenticator`](../src/Api/Auth/GoogleOAuth2Authenticator.php) builds an RS256 JWT assertion from the service-account key and exchanges it for an access token via `https://oauth2.googleapis.com/token`. Tokens are cached in memory and refreshed automatically (with a 60-second safety margin before expiry).

```php
use Jolicode\WalletKit\Api\Auth\GoogleOAuth2Authenticator;
use Jolicode\WalletKit\Api\Credentials\GoogleCredentials;
use Symfony\Component\HttpClient\HttpClient;

$credentials    = new GoogleCredentials('/path/to/service-account.json');
$httpClient     = HttpClient::create();
$authenticator  = new GoogleOAuth2Authenticator($httpClient, $credentials);

$token = $authenticator->getToken(); // TokenInterface
$token->getAccessToken(); // string — Bearer token
$token->isExpired();      // bool
```

You never need to call `getToken()` yourself when using `GoogleWalletClient` -- it handles token acquisition internally on every request.

Requires the `openssl` PHP extension.

---

## CRUD operations

[`GoogleWalletClient`](../src/Api/Google/GoogleWalletClient.php) wraps the Google Wallet REST API (`https://walletobjects.googleapis.com/walletobjects/v1/`).

```php
use Jolicode\WalletKit\Api\Google\GoogleWalletClient;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/** @var NormalizerInterface $normalizer */
$client = new GoogleWalletClient($httpClient, $normalizer, $authenticator);
```

All mutating methods accept a [`GoogleWalletPair`](../src/Builder/GoogleWalletPair.php) that bundles the vertical, issuer class, and pass object. Read methods take a [`GoogleVerticalEnum`](../src/Builder/GoogleVerticalEnum.php) and an ID string.

### Classes

```php
use Jolicode\WalletKit\Builder\GoogleVerticalEnum;

// Create
$response = $client->createClass($pair);

// Read
$response = $client->getClass(GoogleVerticalEnum::GENERIC, '3388000000012345.my_class');

// Full update (PUT)
$response = $client->updateClass($pair);

// Partial update (PATCH)
$response = $client->patchClass($pair);
```

### Objects

```php
$response = $client->createObject($pair);

$response = $client->getObject(GoogleVerticalEnum::GENERIC, '3388000000012345.my_object');

$response = $client->updateObject($pair);

$response = $client->patchObject($pair);
```

### Response handling

Every method returns a [`GoogleApiResponse`](../src/Api/Google/GoogleApiResponse.php):

```php
$response->isSuccessful();  // true when 2xx
$response->getStatusCode(); // int
$response->getData();       // array — decoded JSON body
```

Rate-limited responses (HTTP 429) throw `RateLimitException` with the `Retry-After` value when present. Transport failures throw `HttpRequestException`.

---

## Save links

[`GoogleSaveLinkGenerator`](../src/Api/Google/GoogleSaveLinkGenerator.php) produces an "Add to Google Wallet" URL entirely offline -- no HTTP call is made. The pass object is embedded in a signed JWT appended to `https://pay.google.com/gp/v/save/`.

```php
use Jolicode\WalletKit\Api\Google\GoogleSaveLinkGenerator;

$generator = new GoogleSaveLinkGenerator($normalizer, $credentials);

$url = $generator->generateSaveLink($pair);
// https://pay.google.com/gp/v/save/{jwt}
```

This is useful for distributing passes via email, QR codes, or web buttons without first creating the class/object through the REST API. The JWT is signed with the service-account private key using RS256.

Requires the `openssl` PHP extension.

---

## `createOrUpdatePass()`

A convenience method on `GoogleWalletClient` that handles the typical "ensure class exists, then upsert object" flow:

1. Calls `createClass()`. If the class already exists (HTTP 409), the conflict is silently ignored.
2. Calls `createObject()`. If the object already exists (HTTP 409), falls back to `updateObject()`.
3. Throws `ApiResponseException` on any other non-successful status.

```php
$client->createOrUpdatePass($pair); // void — throws on failure
```

---

## Credentials

[`GoogleCredentials`](../src/Api/Credentials/GoogleCredentials.php) reads and caches the service-account JSON file.

```php
use Jolicode\WalletKit\Api\Credentials\GoogleCredentials;

$credentials = new GoogleCredentials('/path/to/service-account.json');

$data = $credentials->getServiceAccountData(); // array — parsed JSON
// Keys used by the library: 'client_email', 'private_key'
```

The file is read once and kept in memory for the lifetime of the object. Throws `GoogleServiceAccountException` if the file is unreadable or contains invalid JSON.

---

## Full example

Build a generic pass with the builder, push it to Google, and generate a save link.

```php
<?php

declare(strict_types=1);

use Jolicode\WalletKit\Api\Auth\GoogleOAuth2Authenticator;
use Jolicode\WalletKit\Api\Credentials\GoogleCredentials;
use Jolicode\WalletKit\Api\Google\GoogleSaveLinkGenerator;
use Jolicode\WalletKit\Api\Google\GoogleWalletClient;
use Jolicode\WalletKit\Builder\WalletPass;
use Jolicode\WalletKit\Builder\WalletPlatformContext;
use Jolicode\WalletKit\Pass\Android\Model\Generic\GenericTypeEnum;
use Jolicode\WalletKit\Pass\Android\Model\Shared\ReviewStatusEnum;
use Jolicode\WalletKit\Pass\Android\Model\Shared\StateEnum;
use Jolicode\WalletKit\Pass\Apple\Model\Barcode;
use Jolicode\WalletKit\Pass\Apple\Model\BarcodeFormatEnum;
use Jolicode\WalletKit\Pass\Apple\Model\Field;
use Jolicode\WalletKit\Pass\Apple\Model\PassStructure;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

// 1. Setup
$credentials   = new GoogleCredentials('/path/to/service-account.json');
$httpClient    = HttpClient::create();
$authenticator = new GoogleOAuth2Authenticator($httpClient, $credentials);

/** @var NormalizerInterface $normalizer */
$client    = new GoogleWalletClient($httpClient, $normalizer, $authenticator);
$generator = new GoogleSaveLinkGenerator($normalizer, $credentials);

// 2. Build the pass
$context = (new WalletPlatformContext())->withGoogle(
    classId: '3388000000012345.membership_class',
    objectId: '3388000000012345.membership_obj_001',
    issuerName: 'Example Gym',
    defaultReviewStatus: ReviewStatusEnum::APPROVED,
    defaultObjectState: StateEnum::ACTIVE,
);

$built = WalletPass::generic($context)
    ->withPassStructure(new PassStructure(
        primaryFields: [new Field(key: 'member', value: 'Jane Doe', label: 'Member')],
    ))
    ->withGenericType(GenericTypeEnum::GYM_MEMBERSHIP)
    ->withGoogleCardTitle('Gym membership')
    ->addAppleBarcode(new Barcode(
        altText: 'Member ID',
        format: BarcodeFormatEnum::QR,
        message: 'MEM8842',
        messageEncoding: 'utf-8',
    ))
    ->build();

$pair = $built->google();

// 3. Create class + object (upsert)
$client->createOrUpdatePass($pair);

// 4. Generate a save link (offline)
$saveLink = $generator->generateSaveLink($pair);
// https://pay.google.com/gp/v/save/{jwt}

// 5. Later: update the object
$pair->passObject->id = '3388000000012345.membership_obj_001';
$response = $client->patchObject($pair);

if ($response->isSuccessful()) {
    // updated
}
```

---

## Without the bundle

When not using the Symfony bundle, wire the services manually as shown above. The key dependencies are:

| Service | Dependencies |
|---------|-------------|
| `GoogleCredentials` | Path to service-account JSON file |
| `GoogleOAuth2Authenticator` | `HttpClientInterface`, `GoogleCredentials` |
| `GoogleWalletClient` | `HttpClientInterface`, `NormalizerInterface`, `GoogleOAuth2Authenticator` |
| `GoogleSaveLinkGenerator` | `NormalizerInterface`, `GoogleCredentials` |

With the Symfony bundle, add the `google` key to your configuration:

```yaml
# config/packages/wallet_kit.yaml
wallet_kit:
    google:
        serviceAccountJsonPath: '%kernel.project_dir%/config/google-service-account.json'
```

The bundle registers all four services and their class aliases automatically. Inject them by type-hinting `GoogleWalletClient`, `GoogleSaveLinkGenerator`, `GoogleOAuth2Authenticator`, or `GoogleCredentials`.
