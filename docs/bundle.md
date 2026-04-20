# Symfony Bundle

The `WalletKitBundle` integrates wallet-kit into Symfony with conditional service loading, automatic route registration, and throttled bulk operations via Messenger.

## Contents

- [Installation and configuration](#installation-and-configuration)
- [Service registration](#service-registration)
- [WalletContextFactory](#walletcontextfactory)
- [Apple Web Service](#apple-web-service)
- [Google and Samsung callbacks](#google-and-samsung-callbacks)
- [Throttled bulk operations](#throttled-bulk-operations)
- [Issuance helpers](#issuance-helpers)
- [Custom repository implementations](#custom-repository-implementations)
- [Full example](#full-example)

---

## Installation and configuration

```bash
composer require jolicode/wallet-kit
```

Register the bundle if not using Symfony Flex:

```php
// config/bundles.php
return [
    Jolicode\WalletKit\Bundle\WalletKitBundle::class => ['all' => true],
];
```

Create `config/packages/wallet_kit.php`. Each platform node (`apple`, `google`, `samsung`) is optional -- only include the platforms you need:

```php
<?php

use Symfony\Config\WalletKitConfig;

return static function (WalletKitConfig $config): void {
    $config->routePrefix('/wallet-kit'); // default

    $config->apple()
        ->certificatePath('%kernel.project_dir%/var/credentials/pass.p12')
        ->certificatePassword('%env(APPLE_CERTIFICATE_PASSWORD)%')
        ->wwdrCertificatePath(null) // uses bundled WWDR CA by default
        ->apnsKeyPath('%kernel.project_dir%/var/credentials/AuthKey.p8')
        ->apnsKeyId('%env(APPLE_APNS_KEY_ID)%')
        ->apnsTeamId('%env(APPLE_TEAM_ID)%')
        ->teamIdentifier('%env(APPLE_TEAM_ID)%')
        ->passTypeIdentifier('pass.com.example.mypass')
        ->apnsSandbox(false)
        ->pushBatchSize(500)      // max push notifications per batch
        ->pushBatchInterval(300)  // seconds between batches
    ;

    $config->google()
        ->serviceAccountJsonPath('%kernel.project_dir%/var/credentials/google-service-account.json')
        ->apiBatchSize(50)
        ->apiBatchInterval(60)
    ;

    $config->samsung()
        ->partnerId('%env(SAMSUNG_PARTNER_ID)%')
        ->privateKeyPath('%kernel.project_dir%/var/credentials/samsung-private.pem')
        ->serviceId('%env(SAMSUNG_SERVICE_ID)%')
        ->apiBatchSize(100)
        ->apiBatchInterval(30)
    ;
};
```

Source: [`Configuration`](../src/Bundle/DependencyInjection/Configuration.php)

---

## Service registration

Services are registered explicitly (no autowiring). Each platform node in the config triggers loading of its corresponding service definition file. Routes are loaded conditionally based on which platform parameters exist.

Source: [`WalletKitExtension`](../src/Bundle/DependencyInjection/WalletKitExtension.php), [`WalletKitBundle`](../src/Bundle/WalletKitBundle.php)

### Always loaded

| Service ID | Class |
|---|---|
| `wallet_kit.context_factory` | [`WalletContextFactory`](../src/Bundle/WalletContextFactory.php) |

### Apple (when `apple` node is present)

| Service ID | Class |
|---|---|
| `wallet_kit.credentials.apple` | `AppleCredentials` |
| `wallet_kit.auth.apple_apns_jwt` | `AppleApnsJwtProvider` |
| `wallet_kit.apple.packager` | `ApplePassPackager` |
| `wallet_kit.apple.push_notifier` | `ApplePushNotifier` |
| `wallet_kit.controller.apple_web_service` | `AppleWebServiceController` |

### Google (when `google` node is present)

| Service ID | Class |
|---|---|
| `wallet_kit.credentials.google` | `GoogleCredentials` |
| `wallet_kit.auth.google_oauth2` | `GoogleOAuth2Authenticator` |
| `wallet_kit.google.client` | `GoogleWalletClient` |
| `wallet_kit.google.save_link_generator` | `GoogleSaveLinkGenerator` |
| `wallet_kit.controller.google_callback` | `GoogleCallbackController` |

### Samsung (when `samsung` node is present)

| Service ID | Class |
|---|---|
| `wallet_kit.credentials.samsung` | `SamsungCredentials` |
| `wallet_kit.auth.samsung_jwt` | `SamsungJwtAuthenticator` |
| `wallet_kit.samsung.client` | `SamsungWalletClient` |
| `wallet_kit.controller.samsung_callback` | `SamsungCallbackController` |

### Throttling (when `symfony/messenger` is available)

| Service ID | Class |
|---|---|
| `wallet_kit.push.throttled_dispatcher` | `ThrottledPushDispatcher` |
| `wallet_kit.google.throttled_dispatcher` | `ThrottledGoogleDispatcher` |
| `wallet_kit.samsung.throttled_dispatcher` | `ThrottledSamsungDispatcher` |
| `wallet_kit.pending_operation_repository` | `DoctrinePendingOperationRepository` |

All services have class aliases registered, so you can type-hint the class directly for injection.

Source: [`context.php`](../src/Bundle/Resources/config/context.php), [`apple.php`](../src/Bundle/Resources/config/apple.php), [`google.php`](../src/Bundle/Resources/config/google.php), [`samsung.php`](../src/Bundle/Resources/config/samsung.php), [`throttling.php`](../src/Bundle/Resources/config/throttling.php)

---

## WalletContextFactory

[`WalletContextFactory`](../src/Bundle/WalletContextFactory.php) creates a `WalletPlatformContext` pre-filled with Apple defaults (team identifier, pass type identifier, web service URL) derived from your bundle configuration and route generation.

```php
use Jolicode\WalletKit\Bundle\WalletContextFactory;

final class PassController
{
    public function __construct(
        private readonly WalletContextFactory $contextFactory,
    ) {
    }

    public function issue(): Response
    {
        $context = $this->contextFactory->createContext();

        // $context already contains:
        //   - teamIdentifier from config
        //   - passTypeIdentifier from config
        //   - webServiceURL generated from the registered Apple routes
        //
        // Use it with the builder:
        // $builder->build($context);

        // ...
    }
}
```

The factory also exposes `getGoogleCredentials()`, `getSamsungCredentials()`, and `getRoutePrefix()` for direct access to platform credentials.

---

## Apple Web Service

The bundle implements the 5 endpoints required by the [Apple Web Service protocol](https://developer.apple.com/documentation/walletpasses/adding-a-web-service-to-update-passes). Routes are registered automatically when the `apple` config node is present.

Source: [`AppleWebServiceController`](../src/Bundle/Controller/Apple/AppleWebServiceController.php)

### Endpoints

All routes use the configured `route_prefix` (default: `/wallet-kit`).

| Method | Path | Route name | Action |
|---|---|---|---|
| `POST` | `{prefix}/apple/v1/devices/{deviceId}/registrations/{passTypeId}/{serialNumber}` | `wallet_kit_apple_register_device` | Register device |
| `DELETE` | `{prefix}/apple/v1/devices/{deviceId}/registrations/{passTypeId}/{serialNumber}` | `wallet_kit_apple_unregister_device` | Unregister device |
| `GET` | `{prefix}/apple/v1/devices/{deviceId}/registrations/{passTypeId}` | `wallet_kit_apple_serial_numbers` | List serial numbers |
| `GET` | `{prefix}/apple/v1/passes/{passTypeId}/{serialNumber}` | `wallet_kit_apple_latest_pass` | Download latest pass |
| `POST` | `{prefix}/apple/v1/log` | `wallet_kit_apple_log` | Device logs |

Source: [`routes/apple.php`](../src/Bundle/Resources/config/routes/apple.php)

### ApplePassProviderInterface

You must implement [`ApplePassProviderInterface`](../src/Bundle/Apple/ApplePassProviderInterface.php) and register it as a service. The controller depends on it to serve pass data.

```php
use Jolicode\WalletKit\Builder\BuiltWalletPass;
use Jolicode\WalletKit\Bundle\Apple\ApplePassProviderInterface;

final class MyApplePassProvider implements ApplePassProviderInterface
{
    public function getPass(string $passTypeIdentifier, string $serialNumber): BuiltWalletPass
    {
        // Look up the pass by serial number in your database,
        // build it via the WalletPassBuilder, and return the result.
    }

    /**
     * @return array<string, string> filename => local file path
     */
    public function getPassImages(string $passTypeIdentifier, string $serialNumber): array
    {
        return [
            'icon.png' => '/path/to/icon.png',
            'icon@2x.png' => '/path/to/icon@2x.png',
            'logo.png' => '/path/to/logo.png',
        ];
    }

    /**
     * @return string[] Serial numbers of passes updated since $since
     */
    public function getUpdatedSerialNumbers(string $passTypeIdentifier, \DateTimeInterface $since): array
    {
        // Query your database for passes updated after $since
        return ['serial-001', 'serial-042'];
    }
}
```

Register it in your service configuration:

```php
// config/services.php
$services->set(ApplePassProviderInterface::class, MyApplePassProvider::class)
    ->args([/* your dependencies */]);
```

### PassRegistrationRepositoryInterface

[`PassRegistrationRepositoryInterface`](../src/Bundle/Repository/PassRegistrationRepositoryInterface.php) handles device registration persistence. The bundle provides [`DoctrinePassRegistrationRepository`](../src/Bundle/Repository/DoctrinePassRegistrationRepository.php) as a default implementation -- you must register it yourself:

```php
use Jolicode\WalletKit\Bundle\Repository\DoctrinePassRegistrationRepository;
use Jolicode\WalletKit\Bundle\Repository\PassRegistrationRepositoryInterface;

$services->set(PassRegistrationRepositoryInterface::class, DoctrinePassRegistrationRepository::class)
    ->args([service('doctrine.orm.entity_manager')]);
```

### PassRegistration entity

The [`PassRegistration`](../src/Bundle/Entity/PassRegistration.php) Doctrine entity stores device registrations in the `wallet_kit_pass_registration` table with a unique constraint on `(device_id, pass_type_id, serial_number)`.

Columns: `id`, `device_id`, `pass_type_id`, `serial_number`, `push_token`, `registered_at`.

Run `php bin/console doctrine:schema:update` (or create a migration) to create the table.

---

## Google and Samsung callbacks

### Google callback

Route: `POST {prefix}/google/callback` (route name: `wallet_kit_google_callback`)

The controller parses the `eventType` field from the JSON body and dispatches to your [`GoogleCallbackHandlerInterface`](../src/Bundle/Google/GoogleCallbackHandlerInterface.php) implementation. If no handler is registered, the endpoint returns `200 OK` silently.

```php
use Jolicode\WalletKit\Bundle\Google\GoogleCallbackHandlerInterface;

final class MyGoogleCallbackHandler implements GoogleCallbackHandlerInterface
{
    public function onPassSaved(string $classId, string $objectId): void
    {
        // A user saved the pass to their Google Wallet
    }

    public function onPassDeleted(string $classId, string $objectId): void
    {
        // A user deleted the pass from their Google Wallet
    }
}
```

Register it:

```php
$services->set(GoogleCallbackHandlerInterface::class, MyGoogleCallbackHandler::class);
```

Source: [`GoogleCallbackController`](../src/Bundle/Controller/Google/GoogleCallbackController.php)

### Samsung callback

Route: `POST {prefix}/samsung/callback` (route name: `wallet_kit_samsung_callback`)

Same pattern. Implement [`SamsungCallbackHandlerInterface`](../src/Bundle/Samsung/SamsungCallbackHandlerInterface.php) if you want to react to card state changes. If no handler is registered, the endpoint returns `200 OK`.

```php
use Jolicode\WalletKit\Bundle\Samsung\SamsungCallbackHandlerInterface;

final class MySamsungCallbackHandler implements SamsungCallbackHandlerInterface
{
    public function onCardStateChanged(string $cardId, string $newState): void
    {
        // Card state changed (e.g., "activated", "suspended")
    }
}
```

Register it:

```php
$services->set(SamsungCallbackHandlerInterface::class, MySamsungCallbackHandler::class);
```

Source: [`SamsungCallbackController`](../src/Bundle/Controller/Samsung/SamsungCallbackController.php)

---

## Throttled bulk operations

When `symfony/messenger` is available, the bundle registers three dispatchers that queue operations as [`PendingOperation`](../src/Bundle/Entity/PendingOperation.php) entities in the database and process them in batches through Messenger. This prevents hitting platform API rate limits.

Throttling requires Doctrine (for the `wallet_kit_pending_operation` table) and `symfony/messenger`.

### Configuration

Batch sizes and intervals are configured per platform in your `wallet_kit.php` config (see [Installation and configuration](#installation-and-configuration)):

| Platform | `batchSize` default | `batchInterval` default (seconds) |
|---|---|---|
| Apple | 500 | 300 |
| Google | 50 | 60 |
| Samsung | 100 | 30 |

### Dispatchers

**ThrottledPushDispatcher** (Apple) -- sends APNs push notifications to tell devices to update their passes:

```php
use Jolicode\WalletKit\Bundle\Push\ThrottledPushDispatcher;

final class PassUpdateController
{
    public function __construct(
        private readonly ThrottledPushDispatcher $pushDispatcher,
    ) {
    }

    public function notifyUpdate(string $passTypeId, string $serialNumber): void
    {
        // Enqueues push notifications for all registered devices
        $count = $this->pushDispatcher->dispatchUpdateNotifications($passTypeId, $serialNumber);
    }
}
```

Source: [`ThrottledPushDispatcher`](../src/Bundle/Push/ThrottledPushDispatcher.php)

**ThrottledGoogleDispatcher** -- batches Google Wallet API calls:

```php
use Jolicode\WalletKit\Builder\GoogleWalletPair;
use Jolicode\WalletKit\Bundle\Google\ThrottledGoogleDispatcher;

/** @var GoogleWalletPair[] $pairs */
$batchGroupId = $googleDispatcher->dispatchBulkCreateOrUpdate($pairs);
```

Source: [`ThrottledGoogleDispatcher`](../src/Bundle/Google/ThrottledGoogleDispatcher.php)

**ThrottledSamsungDispatcher** -- batches Samsung Wallet API calls:

```php
use Jolicode\WalletKit\Pass\Samsung\Model\Card;
use Jolicode\WalletKit\Bundle\Samsung\ThrottledSamsungDispatcher;

/** @var Card[] $cards */
$batchGroupId = $samsungDispatcher->dispatchBulkCreate($cards);

/** @var array<string, Card> $updates cardId => Card */
$batchGroupId = $samsungDispatcher->dispatchBulkUpdate($updates);
```

Source: [`ThrottledSamsungDispatcher`](../src/Bundle/Samsung/ThrottledSamsungDispatcher.php)

### Messenger transport setup

Route the `ProcessPendingOperationsMessage` to an async transport:

```php
// config/packages/messenger.php
use Jolicode\WalletKit\Bundle\Messenger\ProcessPendingOperationsMessage;

return static function (\Symfony\Config\FrameworkConfig $framework): void {
    $messenger = $framework->messenger();

    $messenger->transport('wallet_kit')
        ->dsn('%env(MESSENGER_TRANSPORT_DSN)%')
    ;

    $messenger->routing(ProcessPendingOperationsMessage::class)
        ->senders(['wallet_kit'])
    ;
};
```

Run the consumer:

```bash
php bin/console messenger:consume wallet_kit
```

The handler ([`ProcessPendingOperationsHandler`](../src/Bundle/Messenger/ProcessPendingOperationsHandler.php)) fetches pending operations from the database in batches, processes them via the appropriate platform processor, and re-dispatches itself with a delay if more operations remain.

---

## Issuance helpers

[`IssuanceHelper`](../src/Api/IssuanceHelper.php) generates "Add to Wallet" URLs for each platform:

```php
use Jolicode\WalletKit\Api\IssuanceHelper;

$helper = new IssuanceHelper($googleSaveLinkGenerator);

// Apple: pass through your own .pkpass download URL
$appleUrl = $helper->appleAddToWalletUrl('https://example.com/passes/download/abc123');

// Google: generates a JWT-based save link
$googleUrl = $helper->googleAddToWalletUrl($googleWalletPair);

// Samsung: generates a deep link
$samsungUrl = $helper->samsungAddToWalletUrl($cardId, $partnerId);
```

---

## Custom repository implementations

To replace the default Doctrine implementations, implement the interface and register your service:

```php
use Jolicode\WalletKit\Bundle\Repository\PassRegistrationRepositoryInterface;

final class RedisPassRegistrationRepository implements PassRegistrationRepositoryInterface
{
    public function register(string $deviceId, string $passTypeId, string $serialNumber, string $pushToken): void
    {
        // ...
    }

    public function unregister(string $deviceId, string $passTypeId, string $serialNumber): void
    {
        // ...
    }

    public function findPushTokens(string $passTypeId, string $serialNumber): array
    {
        // ...
    }

    public function findSerialNumbers(string $deviceId, string $passTypeId): array
    {
        // ...
    }

    public function unregisterByPushToken(string $pushToken): void
    {
        // ...
    }
}
```

Register it as the interface:

```php
$services->set(PassRegistrationRepositoryInterface::class, RedisPassRegistrationRepository::class)
    ->args([/* ... */]);
```

The same applies to [`PendingOperationRepositoryInterface`](../src/Bundle/Repository/PendingOperationRepositoryInterface.php) if you want to replace the default [`DoctrinePendingOperationRepository`](../src/Bundle/Repository/DoctrinePendingOperationRepository.php).

---

## Full example

A Symfony controller that issues passes for all three platforms and handles throttled updates:

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use Jolicode\WalletKit\Api\Apple\ApplePassPackager;
use Jolicode\WalletKit\Api\Google\GoogleSaveLinkGenerator;
use Jolicode\WalletKit\Api\Samsung\SamsungWalletClient;
use Jolicode\WalletKit\Builder\WalletPassBuilder;
use Jolicode\WalletKit\Bundle\Google\ThrottledGoogleDispatcher;
use Jolicode\WalletKit\Bundle\Push\ThrottledPushDispatcher;
use Jolicode\WalletKit\Bundle\Samsung\ThrottledSamsungDispatcher;
use Jolicode\WalletKit\Bundle\WalletContextFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class WalletController
{
    public function __construct(
        private readonly WalletContextFactory $contextFactory,
        private readonly WalletPassBuilder $builder,
        private readonly ApplePassPackager $applePackager,
        private readonly GoogleSaveLinkGenerator $googleSaveLink,
        private readonly SamsungWalletClient $samsungClient,
        private readonly ThrottledPushDispatcher $pushDispatcher,
        private readonly ThrottledGoogleDispatcher $googleDispatcher,
        private readonly ThrottledSamsungDispatcher $samsungDispatcher,
    ) {
    }

    public function issuePass(string $userId): JsonResponse
    {
        $context = $this->contextFactory->createContext();

        $built = $this->builder
            ->boardingPass()
            ->description('Flight to Paris')
            ->serialNumber('flight-' . $userId)
            // ... add fields, colors, etc.
            ->build($context)
        ;

        // Apple: package and return a download URL
        $pkpass = $this->applePackager->package($built->apple(), [
            'icon.png' => '/path/to/icon.png',
        ]);
        // Store $pkpass and generate a download URL...

        // Google: generate a save link
        $googleUrl = $this->googleSaveLink->generateSaveLink($built->google());

        // Samsung: create the card via API
        $samsungCard = $built->samsung();
        $this->samsungClient->createCard($samsungCard);

        return new JsonResponse([
            'google_save_url' => $googleUrl,
        ]);
    }

    public function updateAllPasses(string $passTypeId, string $serialNumber): Response
    {
        // Apple: notify all registered devices to re-download the pass
        $this->pushDispatcher->dispatchUpdateNotifications($passTypeId, $serialNumber);

        return new Response('', Response::HTTP_ACCEPTED);
    }
}
```
