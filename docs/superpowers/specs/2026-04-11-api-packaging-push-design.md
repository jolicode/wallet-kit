# API Clients, Apple Packaging & Push Notifications — Design Spec

## Context

wallet-kit currently produces JSON payloads for Apple Wallet, Google Wallet, and Samsung Wallet via fluent builders and Symfony Serializer normalizers. It does **not** sign Apple passes, bundle `.pkpass` files, call Google/Samsung APIs, or send push notifications.

This spec adds a complete operational layer: API clients for Google and Samsung, `.pkpass` packaging for Apple, push notifications for Apple, and a Symfony Bundle that provides HTTP endpoints for Apple Web Service callbacks and Google/Samsung webhooks.

**Goals:**
- CRUD operations for all three platforms
- Apple `.pkpass` packaging with PKCS7 signing (inspired by [php-pkpass](https://github.com/tschoffelen/php-pkpass))
- Apple push notifications with batch support (inspired by [pushok](https://github.com/edamov/pushok))
- Symfony Bundle with auto-configured routes, DI, and a context factory that pre-fills infrastructure values
- Zero impact on existing pure-modeling usage — no new required dependencies

---

## Architecture: Layered Services (Approach B)

Each platform concern gets its own focused service class. Offline operations (packaging, save links) have no HTTP dependency. Auth is isolated and testable. The existing builder/model/normalizer layer is untouched.

---

## Namespace Layout

```
src/
  # EXISTING (unchanged)
  Builder/
  Pass/
  Common/
  Exception/

  # NEW: API Layer
  Api/
    Credentials/
      AppleCredentials.php
      GoogleCredentials.php
      SamsungCredentials.php
    Auth/
      TokenInterface.php
      CachedToken.php
      GoogleOAuth2Authenticator.php
      SamsungJwtAuthenticator.php
      AppleApnsJwtProvider.php
    Apple/
      ApplePassPackager.php
      ApplePushNotifier.php
      ApnsPushResponse.php
      Resources/
        AppleWWDRCAG4.cer
    Google/
      GoogleWalletClient.php
      GoogleSaveLinkGenerator.php
      GoogleApiResponse.php
    Samsung/
      SamsungWalletClient.php
      SamsungApiResponse.php

  # NEW: API Exceptions
  Exception/
    Api/
      AuthenticationException.php
      HttpRequestException.php
      ApiResponseException.php
      RateLimitException.php
      PackagingException.php
      MissingExtensionException.php

  # NEW: Symfony Bundle
  Bundle/
    WalletKitBundle.php
    WalletContextFactory.php
    DependencyInjection/
      WalletKitExtension.php
      Configuration.php
    Controller/
      Apple/
        AppleWebServiceController.php
      Google/
        GoogleCallbackController.php
      Samsung/
        SamsungCallbackController.php
    Apple/
      ApplePassProviderInterface.php
    Google/
      GoogleCallbackHandlerInterface.php
    Samsung/
      SamsungCallbackHandlerInterface.php
    Repository/
      PassRegistrationRepositoryInterface.php
      DoctrinePassRegistrationRepository.php
    Entity/
      PassRegistration.php
    Resources/
      config/
        routes/
          apple.php          # Apple Web Service routes (register/unregister device, get pass, log)
          google.php         # Google callback route
          samsung.php        # Samsung callback route
        apple.php            # Apple services: packager, push notifier, APNS JWT provider, web service controller
        google.php           # Google services: wallet client, save link generator, OAuth2 authenticator, callback controller
        samsung.php          # Samsung services: wallet client, JWT authenticator, callback controller
        throttling.php       # Throttled operations: pending operation repo, processors, handler, dispatchers
        context.php          # WalletContextFactory
```

---

## Credentials (Value Objects)

### AppleCredentials

```php
final class AppleCredentials
{
    public function __construct(
        // For .pkpass signing (P12 certificate)
        public readonly string $certificatePath,
        public readonly string $certificatePassword,
        public readonly ?string $wwdrCertificatePath = null, // null = built-in WWDR CA

        // For APNS push (P8 key) — optional, only needed for push
        public readonly ?string $apnsKeyPath = null,
        public readonly ?string $apnsKeyId = null,
        public readonly ?string $apnsTeamId = null,

        // For bundle context factory
        public readonly ?string $teamIdentifier = null,
        public readonly ?string $passTypeIdentifier = null,
    ) {}
}
```

### GoogleCredentials

```php
final class GoogleCredentials
{
    public function __construct(
        public readonly string $serviceAccountJsonPath,
    ) {}

    /** @return array<string, mixed> Lazy-loaded and cached */
    public function getServiceAccountData(): array;
}
```

### SamsungCredentials

```php
final class SamsungCredentials
{
    public function __construct(
        public readonly string $partnerId,
        public readonly string $privateKeyPath,
        public readonly ?string $serviceId = null,
    ) {}
}
```

---

## Auth Layer

### TokenInterface

```php
interface TokenInterface
{
    public function getAccessToken(): string;
    public function isExpired(): bool;
}
```

### CachedToken

In-memory token storage with expiry tracking. No external cache dependency.

### GoogleOAuth2Authenticator

1. Creates a JWT signed RS256 with the service account private key
2. POSTs to `https://oauth2.googleapis.com/token` with `grant_type=urn:ietf:params:oauth:grant-type:jwt-bearer`
3. Caches the access token in memory until expiry (~1 hour)
4. Scope: `https://www.googleapis.com/auth/wallet_object.issuer`

### SamsungJwtAuthenticator

Creates a signed JWT with partner credentials for Samsung API authorization.

### AppleApnsJwtProvider

Creates an ES256 JWT for APNS HTTP/2 authentication using the P8 key. Caches for ~50 minutes (Apple tokens expire after 1 hour).

---

## Apple .pkpass Packager

Inspired by [php-pkpass](https://github.com/tschoffelen/php-pkpass).

```php
final class ApplePassPackager
{
    public function __construct(
        private readonly NormalizerInterface $normalizer,
        private readonly AppleCredentials $credentials,
    ) {}

    /**
     * @param Pass                  $pass   The Apple pass model
     * @param array<string, string> $images Filename => binary content (icon.png, logo.png, etc.)
     * @param array<string, array<string, string>> $localizations Locale => [key => value] for .lproj/pass.strings
     * @return string Raw .pkpass binary (ZIP)
     */
    public function package(Pass $pass, array $images, array $localizations = []): string;
}
```

**Internal steps of `package()`:**
1. Normalize `$pass` to array via `$normalizer->normalize()`, then `json_encode()` → `pass.json`
2. Collect all files: `pass.json` + `$images`
3. If `$localizations` is provided, generate `.lproj/pass.strings` files for each locale (e.g., `fr.lproj/pass.strings` with `"key" = "value";` format) and add them to the file collection
4. Compute SHA1 hash of each file → build `manifest.json`
5. Load P12 certificate via `openssl_pkcs12_read()`
6. Sign `manifest.json` via `openssl_pkcs7_sign()` with P12 cert + WWDR CA chain → DER-encoded `signature`
7. Create ZIP archive in memory via `ZipArchive` (temp file) containing all files + `manifest.json` + `signature`
8. Return the ZIP binary string

**Localization support:**
The `$localizations` parameter accepts locale-keyed string tables:
```php
$pkpass = $packager->package($pass, $images, localizations: [
    'en' => ['greeting' => 'Hello', 'store_name' => 'My Store'],
    'fr' => ['greeting' => 'Bonjour', 'store_name' => 'Mon Magasin'],
]);
// Produces: en.lproj/pass.strings, fr.lproj/pass.strings in the ZIP
```
Localized images can be included via the `$images` parameter using the `.lproj/` prefix:
```php
$images = [
    'icon.png'              => file_get_contents('assets/icon.png'),
    'logo.png'              => file_get_contents('assets/logo.png'),
    'fr.lproj/logo.png'     => file_get_contents('assets/fr/logo.png'),
];
```

**Built-in resource:** `AppleWWDRCAG4.cer` ships in `src/Api/Apple/Resources/`. Used as the intermediate CA unless the user provides a custom one via `AppleCredentials::$wwdrCertificatePath`.

**Requires:** `ext-openssl`, `ext-zip`. Throws `MissingExtensionException` at construction if missing.

---

## Apple Push Notifier

Inspired by [pushok](https://github.com/edamov/pushok). Uses Symfony HttpClient for HTTP/2 with concurrent streaming instead of cURL multi.

```php
final class ApplePushNotifier
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly AppleApnsJwtProvider $jwtProvider,
        private readonly bool $sandbox = false,
    ) {}

    public function sendUpdateNotification(string $pushToken, string $passTypeId): ApnsPushResponse;

    /** @return ApnsPushResponse[] */
    public function sendBatchUpdateNotifications(array $pushTokens, string $passTypeId): array;
}
```

**ApnsPushResponse:**

```php
final class ApnsPushResponse
{
    public function isSuccessful(): bool;           // 200
    public function isDeviceTokenInactive(): bool;  // 410 Gone
    public function isRateLimited(): bool;           // 429
    public function getPushToken(): string;
    public function getStatusCode(): int;
    public function getErrorReason(): ?string;
    public function getApnsId(): ?string;
}
```

**Push mechanics:**
- Endpoint: `https://api.push.apple.com/3/device/{pushToken}` (or `api.sandbox.push.apple.com`)
- Headers: `authorization: bearer {jwt}`, `apns-topic: {passTypeId}`, `apns-push-type: background`
- Body: `{}` (empty — triggers device to re-fetch pass from web service)
- Batch uses `$httpClient->stream($responses)` for HTTP/2 multiplexing

---

## Google Wallet Client

```php
final class GoogleWalletClient
{
    private const BASE_URL = 'https://walletobjects.googleapis.com/walletobjects/v1/';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly NormalizerInterface $normalizer,
        private readonly GoogleOAuth2Authenticator $authenticator,
    ) {}

    // Class operations
    public function createClass(GoogleWalletPair $pair): GoogleApiResponse;
    public function getClass(GoogleVerticalEnum $vertical, string $classId): GoogleApiResponse;
    public function updateClass(GoogleWalletPair $pair): GoogleApiResponse;
    public function patchClass(GoogleWalletPair $pair): GoogleApiResponse;

    // Object operations
    public function createObject(GoogleWalletPair $pair): GoogleApiResponse;
    public function getObject(GoogleVerticalEnum $vertical, string $objectId): GoogleApiResponse;
    public function updateObject(GoogleWalletPair $pair): GoogleApiResponse;
    public function patchObject(GoogleWalletPair $pair): GoogleApiResponse;

    // Convenience
    public function createOrUpdatePass(GoogleWalletPair $pair): void;
}
```

**URL mapping:** `GoogleVerticalEnum::FLIGHT` → `flightClass`/`flightObject`, `LOYALTY` → `loyaltyClass`/`loyaltyObject`, etc.

**GoogleApiResponse:**

```php
final class GoogleApiResponse
{
    public function isSuccessful(): bool;  // 2xx
    public function getStatusCode(): int;
    public function getData(): array;      // decoded JSON
}
```

### GoogleSaveLinkGenerator (offline, no HTTP)

```php
final class GoogleSaveLinkGenerator
{
    public function __construct(
        private readonly NormalizerInterface $normalizer,
        private readonly GoogleCredentials $credentials,
    ) {}

    /** @return string https://pay.google.com/gp/v/save/{jwt} */
    public function generateSaveLink(GoogleWalletPair $pair): string;
}
```

Builds a JWT with payload `{"iss": "...", "aud": "google", "typ": "savetowallet", "payload": {"genericObjects": [...]}}`, signs RS256 with service account key.

---

## Samsung Wallet Client

```php
final class SamsungWalletClient
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly NormalizerInterface $normalizer,
        private readonly SamsungJwtAuthenticator $authenticator,
    ) {}

    public function createCard(Card $card): SamsungApiResponse;
    public function getCard(string $cardId): SamsungApiResponse;
    public function updateCard(Card $card, string $cardId): SamsungApiResponse;
    public function updateCardState(string $cardId, string $state): SamsungApiResponse;

    /**
     * Send push notification to update card on user's device.
     * Samsung handles push delivery when cards are updated via the Partner API,
     * but explicit push can be triggered for state changes.
     */
    public function pushCardUpdate(string $cardId): SamsungApiResponse;
}
```

**Samsung push updates:** Unlike Apple (where push is a separate APNS call), Samsung delivers push notifications as part of the Partner API. Calling `updateCard()` or `updateCardState()` triggers Samsung to push the update to the user's device. The explicit `pushCardUpdate()` method is available for cases where you need to re-push without changing data.

---

## Issuance UX Helpers

Helpers for generating platform-specific "Add to Wallet" links and buttons.

```php
namespace Jolicode\WalletKit\Api;

final class IssuanceHelper
{
    public function __construct(
        private readonly ?GoogleSaveLinkGenerator $googleSaveLinkGen = null,
    ) {}

    /**
     * Apple: returns a URL to download the .pkpass file (your own endpoint).
     * Typically the route serving the packaged .pkpass.
     */
    public function appleAddToWalletUrl(string $passDownloadUrl): string;

    /**
     * Google: generates a save link with the JWT-encoded pass.
     * @return string https://pay.google.com/gp/v/save/{jwt}
     */
    public function googleAddToWalletUrl(GoogleWalletPair $pair): string;

    /**
     * Samsung: generates a deep link to add the card.
     * Uses the Samsung Wallet deep link scheme.
     * @return string https://a.]]wallet.samsung.com/...
     */
    public function samsungAddToWalletUrl(string $cardId, string $partnerId): string;
}
```

---

## Exception Hierarchy

All new exceptions implement the existing `WalletKitException` marker interface.

```
WalletKitException (interface, existing)
  AuthenticationException        — OAuth2/JWT failures
  HttpRequestException           — wraps Symfony HttpClient transport errors
  ApiResponseException           — non-2xx with statusCode + responseBody
    RateLimitException           — 429 with retryAfterSeconds
  PackagingException             — OpenSSL / ZipArchive failures
  MissingExtensionException      — ext-openssl or ext-zip not loaded (LogicException)
```

**Retry policy:** The library does NOT implement automatic retries. `RateLimitException` exposes `$retryAfterSeconds` parsed from the response. The calling application owns retry policy.

---

## Symfony Bundle

### Bundle Configuration

```php
// config/packages/wallet_kit.php
return static function (WalletKitConfig $config) {
    $config->apple()
        ->certificatePath('%kernel.project_dir%/var/certs/pass.p12')
        ->certificatePassword('%env(APPLE_CERT_PASSWORD)%')
        ->apnsKeyPath('%kernel.project_dir%/var/certs/apns.p8')
        ->apnsKeyId('%env(APPLE_APNS_KEY_ID)%')
        ->apnsTeamId('%env(APPLE_TEAM_ID)%')
        ->teamIdentifier('%env(APPLE_TEAM_ID)%')
        ->passTypeIdentifier('%env(APPLE_PASS_TYPE_ID)%');

    $config->google()
        ->serviceAccountJsonPath('%kernel.project_dir%/var/certs/google-sa.json');

    $config->samsung()
        ->partnerId('%env(SAMSUNG_PARTNER_ID)%')
        ->privateKeyPath('%kernel.project_dir%/var/certs/samsung.pem');

    $config->routePrefix('/wallet-kit');
};
```

### Service Registration (no autowiring)

The bundle does **not** use autowiring. All services are explicitly defined in PHP config files organized by theme. The `WalletKitExtension` conditionally loads each file based on the bundle configuration:

```php
// DependencyInjection/WalletKitExtension.php
final class WalletKitExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $loader->load('context.php');   // always loaded

        if (\array_key_exists('apple', $config)) {
            $loader->load('apple.php');
        }
        if (\array_key_exists('google', $config)) {
            $loader->load('google.php');
        }
        if (\array_key_exists('samsung', $config)) {
            $loader->load('samsung.php');
        }

        // Only load throttling if Messenger is available AND at least one platform is configured
        if (ContainerBuilder::willBeAvailable('symfony/messenger', MessageBusInterface::class, [])) {
            $loader->load('throttling.php');
        }
    }
}
```

**Example: `Resources/config/apple.php`**

```php
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('wallet_kit.credentials.apple', AppleCredentials::class)
        ->args([
            param('wallet_kit.apple.certificate_path'),
            param('wallet_kit.apple.certificate_password'),
            null, // wwdrCertificatePath — uses built-in
            param('wallet_kit.apple.apns_key_path'),
            param('wallet_kit.apple.apns_key_id'),
            param('wallet_kit.apple.apns_team_id'),
            param('wallet_kit.apple.team_identifier'),
            param('wallet_kit.apple.pass_type_identifier'),
        ]);

    $services->set('wallet_kit.auth.apple_apns_jwt', AppleApnsJwtProvider::class)
        ->args([service('wallet_kit.credentials.apple')]);

    $services->set('wallet_kit.apple.packager', ApplePassPackager::class)
        ->args([
            service('serializer'),
            service('wallet_kit.credentials.apple'),
        ])
        ->alias(ApplePassPackager::class, 'wallet_kit.apple.packager');

    $services->set('wallet_kit.apple.push_notifier', ApplePushNotifier::class)
        ->args([
            service('http_client'),
            service('wallet_kit.auth.apple_apns_jwt'),
            param('wallet_kit.apple.apns_sandbox'),
        ])
        ->alias(ApplePushNotifier::class, 'wallet_kit.apple.push_notifier');

    $services->set('wallet_kit.controller.apple_web_service', AppleWebServiceController::class)
        ->args([
            service('wallet_kit.repository.pass_registration'),
            service('wallet_kit.apple.pass_provider'),
            service('wallet_kit.apple.packager'),
        ])
        ->tag('controller.service_arguments');
};
```

**Example: `Resources/config/throttling.php`**

```php
return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('wallet_kit.repository.pending_operation', DoctrinePendingOperationRepository::class)
        ->args([service('doctrine.orm.entity_manager')]);

    $services->set('wallet_kit.processor.apple_push', ApplePushProcessor::class)
        ->args([
            service('wallet_kit.apple.push_notifier'),
            service('wallet_kit.repository.pass_registration'),
        ])
        ->tag('wallet_kit.pending_operation_processor');

    $services->set('wallet_kit.processor.google_api', GoogleApiProcessor::class)
        ->args([service('wallet_kit.google.client')])
        ->tag('wallet_kit.pending_operation_processor');

    $services->set('wallet_kit.processor.samsung_api', SamsungApiProcessor::class)
        ->args([service('wallet_kit.samsung.client')])
        ->tag('wallet_kit.pending_operation_processor');

    $services->set('wallet_kit.messenger.handler', ProcessPendingOperationsHandler::class)
        ->args([
            service('wallet_kit.repository.pending_operation'),
            service('messenger.default_bus'),
            tagged_iterator('wallet_kit.pending_operation_processor'),
            param('wallet_kit.batch_config'),
        ])
        ->tag('messenger.message_handler');

    $services->set('wallet_kit.dispatcher.apple_push', ThrottledPushDispatcher::class)
        ->args([
            service('messenger.default_bus'),
            service('wallet_kit.repository.pending_operation'),
            service('wallet_kit.repository.pass_registration'),
        ])
        ->alias(ThrottledPushDispatcher::class, 'wallet_kit.dispatcher.apple_push');

    $services->set('wallet_kit.dispatcher.google', ThrottledGoogleDispatcher::class)
        ->args([
            service('messenger.default_bus'),
            service('wallet_kit.repository.pending_operation'),
            service('serializer'),
        ])
        ->alias(ThrottledGoogleDispatcher::class, 'wallet_kit.dispatcher.google');

    $services->set('wallet_kit.dispatcher.samsung', ThrottledSamsungDispatcher::class)
        ->args([
            service('messenger.default_bus'),
            service('wallet_kit.repository.pending_operation'),
            service('serializer'),
        ])
        ->alias(ThrottledSamsungDispatcher::class, 'wallet_kit.dispatcher.samsung');
};
```

Each config file registers services with explicit `->args()`, public aliases for type-hinted injection, and `->tag()` for tagged services. No `autoconfigure()`, no `autowire()`, no `resource()` glob.

### WalletContextFactory

Pre-fills infrastructure values so users only provide business data.

```php
final class WalletContextFactory
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly ?AppleCredentials $appleCredentials,
        private readonly ?GoogleCredentials $googleCredentials,
        private readonly ?SamsungCredentials $samsungCredentials,
        private readonly string $routePrefix,
    ) {}

    /**
     * Creates a WalletPlatformContext pre-configured with:
     * - Apple: teamIdentifier, passTypeIdentifier, webServiceURL, authenticationToken
     * - Google/Samsung: base configuration from credentials
     *
     * The user completes with business-specific values:
     * - Apple: serialNumber, organizationName, description
     * - Google: classId, objectId
     * - Samsung: refId
     */
    public function createContext(): WalletPlatformContext;
}
```

**Usage in a Symfony controller:**

```php
class PassController
{
    public function __construct(
        private readonly WalletContextFactory $contextFactory,
        private readonly ApplePassPackager $packager,
        private readonly GoogleWalletClient $googleClient,
    ) {}

    public function createPass(): Response
    {
        $context = $this->contextFactory->createContext()
            ->withApple(
                serialNumber: 'PASS-001',
                organizationName: 'My Company',
                description: 'Loyalty Card',
            )
            ->withGoogle(
                classId: 'issuer.loyaltyClass',
                objectId: 'issuer.loyaltyObject.001',
            );

        $built = WalletPass::loyalty($context, title: 'Gold Member', ...)->build();

        // Apple: package .pkpass
        $pkpass = $this->packager->package($built->apple(), [
            'icon.png' => file_get_contents('assets/icon.png'),
        ]);

        // Google: create via API
        $this->googleClient->createOrUpdatePass($built->google());

        return new Response($pkpass, 200, [
            'Content-Type' => 'application/vnd.apple.pkpass',
        ]);
    }
}
```

**WalletPlatformContext change:** The existing `withApple()` method needs to support partial construction — accepting pre-filled defaults from the factory and letting the user complete with business values. This is achieved by making the factory set defaults via a new `withAppleDefaults()` method, and having `withApple()` merge on top.

### Route Loading

The bundle loads routes automatically via `loadRoutes()` — no import needed by the user. Routes are split by platform and loaded conditionally based on the bundle configuration:

```php
// WalletKitBundle.php
final class WalletKitBundle extends AbstractBundle
{
    public function loadRoutes(RoutingConfigurator $routes): void
    {
        // Only load routes for configured platforms
        if ($this->hasAppleConfig()) {
            $routes->import(__DIR__.'/Resources/config/routes/apple.php');
        }
        if ($this->hasGoogleConfig()) {
            $routes->import(__DIR__.'/Resources/config/routes/google.php');
        }
        if ($this->hasSamsungConfig()) {
            $routes->import(__DIR__.'/Resources/config/routes/samsung.php');
        }
    }
}
```

**Example: `Resources/config/routes/apple.php`**

```php
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes) {
    $prefix = '%wallet_kit.route_prefix%/apple/v1';

    $routes->add('wallet_kit_apple_register_device', $prefix.'/devices/{deviceId}/registrations/{passTypeId}/{serialNumber}')
        ->controller([AppleWebServiceController::class, 'registerDevice'])
        ->methods(['POST']);

    $routes->add('wallet_kit_apple_unregister_device', $prefix.'/devices/{deviceId}/registrations/{passTypeId}/{serialNumber}')
        ->controller([AppleWebServiceController::class, 'unregisterDevice'])
        ->methods(['DELETE']);

    $routes->add('wallet_kit_apple_serial_numbers', $prefix.'/devices/{deviceId}/registrations/{passTypeId}')
        ->controller([AppleWebServiceController::class, 'getSerialNumbers'])
        ->methods(['GET']);

    $routes->add('wallet_kit_apple_latest_pass', $prefix.'/passes/{passTypeId}/{serialNumber}')
        ->controller([AppleWebServiceController::class, 'getLatestPass'])
        ->methods(['GET']);

    $routes->add('wallet_kit_apple_log', $prefix.'/log')
        ->controller([AppleWebServiceController::class, 'log'])
        ->methods(['POST']);
};
```

### Apple Web Service Endpoints

Routes loaded from `Resources/config/routes/apple.php` (prefix configurable via `$config->routePrefix()`):

| Method | Path | Purpose |
|--------|------|---------|
| POST | `{prefix}/apple/v1/devices/{deviceId}/registrations/{passTypeId}/{serialNumber}` | Register device for pass updates |
| DELETE | `{prefix}/apple/v1/devices/{deviceId}/registrations/{passTypeId}/{serialNumber}` | Unregister device |
| GET | `{prefix}/apple/v1/devices/{deviceId}/registrations/{passTypeId}` | List updated serial numbers |
| GET | `{prefix}/apple/v1/passes/{passTypeId}/{serialNumber}` | Download latest .pkpass |
| POST | `{prefix}/apple/v1/log` | Device error logs |

### User-Provided Interface: ApplePassProviderInterface

```php
interface ApplePassProviderInterface
{
    /** Build the current version of this pass */
    public function getPass(string $passTypeIdentifier, string $serialNumber): BuiltWalletPass;

    /** @return array<string, string> filename => binary content */
    public function getPassImages(string $passTypeIdentifier, string $serialNumber): array;

    /** @return string[] Serial numbers updated since $since */
    public function getUpdatedSerialNumbers(string $passTypeIdentifier, \DateTimeInterface $since): array;
}
```

The user implements this interface and registers it as a Symfony service. The bundle's `AppleWebServiceController` uses it to serve passes on demand. The `getLatestPass` endpoint calls `getPass()` + `getPassImages()`, packages via `ApplePassPackager`, and returns the `.pkpass` binary.

### Persistence: PassRegistrationRepositoryInterface

```php
interface PassRegistrationRepositoryInterface
{
    public function register(string $deviceId, string $passTypeId, string $serialNumber, string $pushToken): void;
    public function unregister(string $deviceId, string $passTypeId, string $serialNumber): void;

    /** @return string[] Push tokens for this pass */
    public function findPushTokens(string $passTypeId, string $serialNumber): array;

    /** @return string[] Serial numbers registered for this device */
    public function findSerialNumbers(string $deviceId, string $passTypeId): array;
}
```

**Default implementation:** `DoctrinePassRegistrationRepository` using a `PassRegistration` entity with Doctrine ORM mapping. User can replace by registering their own service implementing the interface.

### Google/Samsung Callback Handlers

```php
interface GoogleCallbackHandlerInterface
{
    public function onPassSaved(string $classId, string $objectId): void;
    public function onPassDeleted(string $classId, string $objectId): void;
}

interface SamsungCallbackHandlerInterface
{
    public function onCardStateChanged(string $cardId, string $newState): void;
}
```

Optional: if the user does not implement these, callback endpoints return 200 OK with an empty body.

---

## Dependencies (composer.json)

```json
{
    "require": {
        "php": ">=8.3",
        "symfony/serializer": "^7.4 || ^8.0"
    },
    "suggest": {
        "symfony/http-client": "Required for Google/Samsung API clients and Apple push notifications",
        "symfony/framework-bundle": "Required for the Symfony bundle (routes, DI, controllers)",
        "symfony/routing": "Required for the Symfony bundle route generation",
        "symfony/messenger": "Required for the throttled Apple push notification dispatcher",
        "doctrine/orm": "Required for the default PassRegistration and PendingPush Doctrine repositories",
        "ext-openssl": "Required for Apple .pkpass signing and JWT generation",
        "ext-zip": "Required for Apple .pkpass packaging"
    }
}
```

No new required dependencies. All API/bundle features are optional.

---

## Implementation Phases

| Phase | Scope | Dependencies Added |
|-------|-------|--------------------|
| 1 | Credentials value objects + API exception classes | None |
| 2 | Apple .pkpass Packager (signing + ZIP + localization) | ext-openssl, ext-zip |
| 3 | Auth layer (Google OAuth2, Samsung JWT, Apple APNS JWT) | ext-openssl, symfony/http-client |
| 4 | Google Wallet Client + Save Link Generator | Phase 3 |
| 5 | Samsung Wallet Client (CRUD + push updates) | Phase 3 |
| 6 | Apple Push Notifier (single + batch) | Phase 3 |
| 7 | Issuance UX Helpers (Add to Wallet URLs for all 3 platforms) | Phase 4 |
| 8 | Symfony Bundle (config, DI, WalletContextFactory, routes) | symfony/framework-bundle, symfony/routing |
| 9 | Apple Web Service Controller + PassRegistration repository | Phase 8 |
| 10 | Unified Throttled Operations (entity, repo, processors, handler, dispatchers) | Phase 8 + symfony/messenger |
| 11 | Google/Samsung Callback Controllers | Phase 8 |
| 12 | Documentation (README update, docs/apple.md, docs/google.md, docs/samsung.md, docs/bundle.md) | All phases |

---

## Bundle: Throttled Bulk Operations (Unified DB Queue)

### Problem

Sending 2M Apple push notifications at once, or creating thousands of Google/Samsung passes in a tight loop, overwhelms APIs and hits rate limits (429). All three platforms need the same batching mechanism.

### Solution: Single DB Queue with Strategy Pattern

A single table `wallet_kit_pending_operation` stores all pending operations across platforms. A generic Messenger handler dequeues batches and delegates to platform-specific processors via a tagged strategy pattern. Only one `DelayStamp` is active at a time — compatible with all transports (Doctrine, Redis, SQS, AMQP).

### Flow

```
dispatcher->dispatchForTokens(2M tokens, ...)   // or dispatchBulkCreateOrUpdate(pairs)
  → INSERT 2M rows into wallet_kit_pending_operation (platform=APPLE)
  → dispatch 1x ProcessPendingOperationsMessage(platform=APPLE, batchGroupId=...)

Handler ProcessPendingOperationsMessage:
  → resolve processor for platform via tagged strategy
  → dequeue batchSize operations (FIFO)
  → processor->process(operations)
  → on RateLimitException: re-dispatch with DelayStamp(retryAfter)
  → on success + remaining > 0: re-dispatch with DelayStamp(interval)
  → on success + remaining = 0: done
```

### Platform Enum

```php
namespace Jolicode\WalletKit\Bundle;

enum WalletPlatformEnum: string
{
    case APPLE = 'apple';
    case GOOGLE = 'google';
    case SAMSUNG = 'samsung';
}
```

### Entity

```php
namespace Jolicode\WalletKit\Bundle\Entity;

use Jolicode\WalletKit\Pass\Apple\Model\Pass;
use Jolicode\WalletKit\Pass\Android\Model\EventTicket\EventTicketClass;
use Jolicode\WalletKit\Pass\Android\Model\EventTicket\EventTicketObject;
use Jolicode\WalletKit\Pass\Android\Model\Flight\FlightClass;
use Jolicode\WalletKit\Pass\Android\Model\Flight\FlightObject;
use Jolicode\WalletKit\Pass\Android\Model\Generic\GenericClass;
use Jolicode\WalletKit\Pass\Android\Model\Generic\GenericObject;
use Jolicode\WalletKit\Pass\Android\Model\GiftCard\GiftCardClass;
use Jolicode\WalletKit\Pass\Android\Model\GiftCard\GiftCardObject;
use Jolicode\WalletKit\Pass\Android\Model\Loyalty\LoyaltyClass;
use Jolicode\WalletKit\Pass\Android\Model\Loyalty\LoyaltyObject;
use Jolicode\WalletKit\Pass\Android\Model\Offer\OfferClass;
use Jolicode\WalletKit\Pass\Android\Model\Offer\OfferObject;
use Jolicode\WalletKit\Pass\Android\Model\Transit\TransitClass;
use Jolicode\WalletKit\Pass\Android\Model\Transit\TransitObject;
use Jolicode\WalletKit\Pass\Samsung\Model\Card;

/**
 * @phpstan-import-type PassType from Pass
 * @phpstan-import-type CardEnvelopeType from Card
 * @phpstan-import-type FlightClassType from FlightClass
 * @phpstan-import-type FlightObjectType from FlightObject
 * @phpstan-import-type EventTicketClassType from EventTicketClass
 * @phpstan-import-type EventTicketObjectType from EventTicketObject
 * @phpstan-import-type GenericClassType from GenericClass
 * @phpstan-import-type GenericObjectType from GenericObject
 * @phpstan-import-type GiftCardClassType from GiftCardClass
 * @phpstan-import-type GiftCardObjectType from GiftCardObject
 * @phpstan-import-type LoyaltyClassType from LoyaltyClass
 * @phpstan-import-type LoyaltyObjectType from LoyaltyObject
 * @phpstan-import-type OfferClassType from OfferClass
 * @phpstan-import-type OfferObjectType from OfferObject
 * @phpstan-import-type TransitClassType from TransitClass
 * @phpstan-import-type TransitObjectType from TransitObject
 *
 * @phpstan-type GoogleClassPayload FlightClassType|EventTicketClassType|GenericClassType|GiftCardClassType|LoyaltyClassType|OfferClassType|TransitClassType
 * @phpstan-type GoogleObjectPayload FlightObjectType|EventTicketObjectType|GenericObjectType|GiftCardObjectType|LoyaltyObjectType|OfferObjectType|TransitObjectType
 *
 * @phpstan-type ApplePushPayload array{pushToken: string, passTypeId: string}
 * @phpstan-type GoogleApiPayload array{vertical: string, operationType: string, classPayload: GoogleClassPayload, objectPayload: GoogleObjectPayload}
 * @phpstan-type SamsungApiPayload array{operationType: string, cardId: string|null, cardPayload: CardEnvelopeType, newState: string|null}
 * @phpstan-type PendingOperationPayload ApplePushPayload|GoogleApiPayload|SamsungApiPayload
 */
#[ORM\Entity]
#[ORM\Table(name: 'wallet_kit_pending_operation')]
#[ORM\Index(columns: ['batch_group_id', 'id'])]
final class PendingOperation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public ?int $id = null;

    #[ORM\Column(length: 20, enumType: WalletPlatformEnum::class)]
    public WalletPlatformEnum $platform;

    #[ORM\Column(length: 255)]
    public string $batchGroupId;

    /** @var PendingOperationPayload */
    #[ORM\Column(type: 'json')]
    public array $payload;

    #[ORM\Column]
    public \DateTimeImmutable $createdAt;
}
```

**Payload shapes by platform:**

| Platform | Payload type | Fields | Typed payload content |
|----------|-------------|--------|-----------------------|
| `APPLE` | `ApplePushPayload` | `{pushToken, passTypeId}` | Scalar strings only |
| `GOOGLE` | `GoogleApiPayload` | `{vertical, operationType, classPayload, objectPayload}` | `classPayload`: union of all `{Vertical}ClassType` (from `src/Pass/Android/Model/`), `objectPayload`: union of all `{Vertical}ObjectType` |
| `SAMSUNG` | `SamsungApiPayload` | `{operationType, cardId, cardPayload, newState}` | `cardPayload`: `CardEnvelopeType` (from `src/Pass/Samsung/Model/Card.php`) |

### Repository Interface

```php
namespace Jolicode\WalletKit\Bundle\Repository;

interface PendingOperationRepositoryInterface
{
    /** @param PendingOperation[] $operations */
    public function enqueue(array $operations): void;

    /** @return PendingOperation[] */
    public function dequeue(string $batchGroupId, int $limit): array;

    public function countPending(string $batchGroupId): int;
}
```

Default implementation: `DoctrinePendingOperationRepository`.

### Messenger Message

```php
namespace Jolicode\WalletKit\Bundle\Messenger;

final class ProcessPendingOperationsMessage
{
    public function __construct(
        public readonly WalletPlatformEnum $platform,
        public readonly string $batchGroupId,
    ) {}
}
```

### Processor Strategy Interface

```php
namespace Jolicode\WalletKit\Bundle\Processor;

interface PendingOperationProcessorInterface
{
    /** The platform this processor handles */
    public function supports(): WalletPlatformEnum;

    /**
     * Process a batch of operations.
     * @param PendingOperation[] $operations
     * @throws RateLimitException on 429 — handler will re-dispatch with Retry-After delay
     */
    public function process(array $operations): void;
}
```

### Platform Processors

```php
final class ApplePushProcessor implements PendingOperationProcessorInterface
{
    public function __construct(
        private readonly ApplePushNotifier $pushNotifier,
        private readonly PassRegistrationRepositoryInterface $registrationRepo,
    ) {}

    public function supports(): WalletPlatformEnum { return WalletPlatformEnum::APPLE; }

    /** @param PendingOperation[] $operations (each payload is ApplePushPayload) */
    public function process(array $operations): void
    {
        $passTypeId = $operations[0]->payload['passTypeId'];
        $tokens = array_map(fn ($op) => $op->payload['pushToken'], $operations);

        $responses = $this->pushNotifier->sendBatchUpdateNotifications($tokens, $passTypeId);

        foreach ($responses as $response) {
            if ($response->isDeviceTokenInactive()) {
                $this->registrationRepo->unregisterByPushToken($response->getPushToken());
            }
        }
    }
}

final class GoogleApiProcessor implements PendingOperationProcessorInterface
{
    public function __construct(
        private readonly GoogleWalletClient $googleClient,
    ) {}

    public function supports(): WalletPlatformEnum { return WalletPlatformEnum::GOOGLE; }

    /** @param PendingOperation[] $operations (each payload is GoogleApiPayload) */
    public function process(array $operations): void
    {
        foreach ($operations as $operation) {
            // Reconstruct GoogleWalletPair from payload and execute API call
            // based on operationType (create_class, create_object, create_or_update, etc.)
            $this->executeOperation($operation->payload);
        }
    }
}

final class SamsungApiProcessor implements PendingOperationProcessorInterface
{
    public function __construct(
        private readonly SamsungWalletClient $samsungClient,
    ) {}

    public function supports(): WalletPlatformEnum { return WalletPlatformEnum::SAMSUNG; }

    /** @param PendingOperation[] $operations (each payload is SamsungApiPayload) */
    public function process(array $operations): void
    {
        foreach ($operations as $operation) {
            $this->executeOperation($operation->payload);
        }
    }
}
```

### Generic Handler

```php
namespace Jolicode\WalletKit\Bundle\Messenger;

final class ProcessPendingOperationsHandler
{
    /** @var array<string, PendingOperationProcessorInterface> */
    private readonly array $processorMap;

    /**
     * @param iterable<PendingOperationProcessorInterface> $processors
     * @param array<string, array{batchSize: int, batchInterval: int}> $batchConfig
     */
    public function __construct(
        private readonly PendingOperationRepositoryInterface $pendingRepo,
        private readonly MessageBusInterface $messageBus,
        iterable $processors, // tagged_iterator('wallet_kit.pending_operation_processor') via config
        private readonly array $batchConfig,
    ) {
        $map = [];
        foreach ($processors as $processor) {
            $map[$processor->supports()->value] = $processor;
        }
        $this->processorMap = $map;
    }

    public function __invoke(ProcessPendingOperationsMessage $message): void
    {
        $processor = $this->processorMap[$message->platform->value]
            ?? throw new \LogicException(sprintf('No processor for platform "%s"', $message->platform->value));

        $config = $this->batchConfig[$message->platform->value];
        $operations = $this->pendingRepo->dequeue($message->batchGroupId, $config['batchSize']);

        if ([] === $operations) {
            return;
        }

        try {
            $processor->process($operations);
        } catch (RateLimitException $e) {
            // Re-dispatch with Retry-After delay (operations stay in queue since dequeue is atomic)
            $this->messageBus->dispatch(
                new ProcessPendingOperationsMessage($message->platform, $message->batchGroupId),
                [new DelayStamp(($e->retryAfterSeconds ?? $config['batchInterval']) * 1000)],
            );
            return;
        }

        // If more pending, schedule next batch
        if ($this->pendingRepo->countPending($message->batchGroupId) > 0) {
            $this->messageBus->dispatch(
                new ProcessPendingOperationsMessage($message->platform, $message->batchGroupId),
                [new DelayStamp($config['batchInterval'] * 1000)],
            );
        }
    }
}
```

**Note on dequeue semantics:** `dequeue()` must be atomic — it fetches AND removes the operations in a single transaction. This way, on a `RateLimitException`, the failed operations are NOT removed yet (since the exception interrupts before dequeue completes). The re-dispatched message will pick them up again.

### Dispatchers

Each platform has its own dispatcher that knows how to build `PendingOperation` rows with the correct payload shape.

```php
namespace Jolicode\WalletKit\Bundle\Push;

final class ThrottledPushDispatcher
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly PendingOperationRepositoryInterface $pendingRepo,
        private readonly PassRegistrationRepositoryInterface $registrationRepo,
    ) {}

    /**
     * Dispatches throttled push notifications for all devices registered to this pass.
     * @return int Total number of push tokens queued
     */
    public function dispatchUpdateNotifications(string $passTypeId, string $serialNumber): int;

    /**
     * Dispatches throttled push notifications for an explicit list of push tokens.
     * @param string[] $pushTokens
     * @return int Total number of push tokens queued
     */
    public function dispatchForTokens(array $pushTokens, string $passTypeId, string $serialNumber): int;
}
```

```php
namespace Jolicode\WalletKit\Bundle\Google;

final class ThrottledGoogleDispatcher
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly PendingOperationRepositoryInterface $pendingRepo,
        private readonly NormalizerInterface $normalizer,
    ) {}

    /** @param GoogleWalletPair[] $pairs */
    public function dispatchBulkCreateOrUpdate(array $pairs): string;

    /** @param GoogleWalletPair[] $pairs */
    public function dispatchBulkOperation(array $pairs, string $operationType): string;
}
```

```php
namespace Jolicode\WalletKit\Bundle\Samsung;

final class ThrottledSamsungDispatcher
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly PendingOperationRepositoryInterface $pendingRepo,
        private readonly NormalizerInterface $normalizer,
    ) {}

    /** @param Card[] $cards */
    public function dispatchBulkCreate(array $cards): string;

    /** @param array<string, Card> $cards cardId => Card */
    public function dispatchBulkUpdate(array $cards): string;
}
```

All dispatchers:
1. Build `PendingOperation` rows with platform-specific payload shape
2. Insert via `pendingRepo->enqueue()`
3. Dispatch a single `ProcessPendingOperationsMessage` (no delay — first batch immediate)
4. Return the `batchGroupId` (for Google/Samsung) or count (for Apple)

### PassRegistrationRepositoryInterface Addition

```php
interface PassRegistrationRepositoryInterface
{
    // ... existing methods ...

    /** Remove all registrations for this push token (for 410 cleanup) */
    public function unregisterByPushToken(string $pushToken): void;
}
```

### Bundle Configuration

```php
return static function (WalletKitConfig $config) {
    $config->apple()
        // ... credentials ...
        ->pushBatchSize(500)          // default: 500
        ->pushBatchInterval(300);     // default: 300s (5 min)

    $config->google()
        // ... credentials ...
        ->apiBatchSize(50)            // default: 50
        ->apiBatchInterval(60);       // default: 60s

    $config->samsung()
        // ... credentials ...
        ->apiBatchSize(100)           // default: 100
        ->apiBatchInterval(30);       // default: 30s
};
```

### Usage

```php
class PassController
{
    public function __construct(
        private readonly ThrottledPushDispatcher $pushDispatcher,
        private readonly ThrottledGoogleDispatcher $googleDispatcher,
        private readonly ThrottledSamsungDispatcher $samsungDispatcher,
    ) {}

    // Apple: push update to all holders
    public function updateLoyaltyPass(string $serialNumber): Response
    {
        $total = $this->pushDispatcher->dispatchUpdateNotifications(
            'pass.com.example.loyalty', $serialNumber,
        );
        return new JsonResponse(['queued' => $total]);
    }

    // Apple: push to explicit token list
    public function notifySpecificHolders(array $pushTokens): Response
    {
        $total = $this->pushDispatcher->dispatchForTokens(
            $pushTokens, 'pass.com.example.loyalty', 'PASS-001',
        );
        return new JsonResponse(['queued' => $total]);
    }

    // Google/Samsung: bulk provision
    public function provisionPasses(array $members): Response
    {
        $googlePairs = [];
        $samsungCards = [];
        foreach ($members as $member) {
            $built = WalletPass::loyalty($context, ...)->build();
            $googlePairs[] = $built->google();
            $samsungCards[] = $built->samsung();
        }

        $googleBatch = $this->googleDispatcher->dispatchBulkCreateOrUpdate($googlePairs);
        $samsungBatch = $this->samsungDispatcher->dispatchBulkCreate($samsungCards);

        return new JsonResponse([
            'google_batch' => $googleBatch,
            'samsung_batch' => $samsungBatch,
        ]);
    }
}
```

### Dependencies

`symfony/messenger` is added to `suggest` in composer.json. The dispatchers, processors, and handler are only registered if Messenger is available. Without Messenger, users can still use `ApplePushNotifier`, `GoogleWalletClient`, and `SamsungWalletClient` directly for manual control.

---

## Verification Plan

### Unit Tests
- Credentials: construction, validation, lazy loading
- Auth: JWT generation format, token caching/refresh, OAuth2 token exchange (mocked HTTP)
- ApplePassPackager: manifest generation, file hashing, ZIP structure, localized `.lproj/pass.strings` generation (verify with ZipArchive)
- ApnsPushResponse: status code parsing, error reason extraction
- GoogleSaveLinkGenerator: JWT structure and signing (offline, no mocks needed)
- IssuanceHelper: URL generation for all 3 platforms
- Exception hierarchy: all implement WalletKitException

### Integration Tests
- GoogleWalletClient: mock HTTP responses for CRUD operations, verify request URLs and auth headers
- SamsungWalletClient: same pattern
- ApplePushNotifier: mock HTTP, verify batch sends correct number of requests with correct headers
- WalletContextFactory: verify pre-filled context merges correctly with user values

### Bundle Tests (functional)
- Configuration loading and service auto-wiring
- Route registration and URL generation
- AppleWebServiceController: mock PassProvider, verify endpoints return correct responses
- Callback controllers: verify handler invocation or 200-empty fallback
- PendingOperation entity: verify payload phpstan types match platform enum
- PendingOperationRepository: verify enqueue/dequeue atomicity, FIFO ordering, countPending
- ProcessPendingOperationsHandler: verify processor resolution via tagged strategy, verify dequeue + process + re-dispatch chain, verify chain stops when queue empty
- ApplePushProcessor: verify 410 cleanup, batch push calls
- GoogleApiProcessor: verify operation type routing, RateLimitException re-dispatch with Retry-After delay
- SamsungApiProcessor: same pattern as Google
- ThrottledPushDispatcher: verify enqueue with ApplePushPayload shape, dispatches initial message
- ThrottledGoogleDispatcher: verify enqueue normalizes pairs to GoogleApiPayload, dispatches initial message
- ThrottledSamsungDispatcher: same pattern as Google

### Manual Verification
- Package a .pkpass and open it on an iOS device or macOS
- Create a Google Wallet class+object via API and generate a save link
- Send an Apple push notification to a registered device
- Test the full Apple Web Service flow: add pass → register device → update pass → push → device re-fetches

---

## Documentation Updates

### README.md Updates

Update the README to mention the new capabilities without going into detail. Replace the "Next steps" section with a features overview and link to dedicated docs. Keep it concise — the README should tell the reader *what's possible*, the docs explain *how*.

**New sections to add after "Package layout":**

```markdown
## Beyond payloads

The library also handles the operational side of wallet passes:

- **Apple `.pkpass` packaging** — sign and bundle passes into `.pkpass` files with localization support
- **Google Wallet API** — create, update, and patch classes and objects, generate "Add to Google Wallet" save links
- **Samsung Wallet API** — create, update, and manage card lifecycle via the Partner API
- **Apple push notifications** — trigger pass refreshes via APNs HTTP/2 with batch support
- **Throttled bulk operations** — DB-backed queue with configurable batch size and interval for large-scale operations
- **Symfony Bundle** — auto-configured services, Apple Web Service endpoints, Google/Samsung callbacks, and a context factory that pre-fills infrastructure values

See platform-specific guides: [Apple](docs/apple.md) · [Google](docs/google.md) · [Samsung](docs/samsung.md) · [Symfony Bundle](docs/bundle.md)
```

**Update "Package layout"** to add the new namespaces:

```markdown
- `Jolicode\WalletKit\Api\Apple` — `.pkpass` packaging, APNs push notifications
- `Jolicode\WalletKit\Api\Google` — Google Wallet REST API client, save link generation
- `Jolicode\WalletKit\Api\Samsung` — Samsung Wallet Partner API client
- `Jolicode\WalletKit\Api\Auth` — OAuth2/JWT authenticators for Google, Samsung, Apple APNs
- `Jolicode\WalletKit\Api\Credentials` — Credential value objects for each platform
- `Jolicode\WalletKit\Bundle` — Symfony Bundle with DI, routes, controllers, and throttled dispatchers
```

**Simplify "Next steps"** to only keep items not yet covered (e.g., operational tooling).

### New Documentation Files

#### `docs/apple.md` — Apple Wallet: Packaging & Updates

Contents:
1. **`.pkpass` packaging** — how to use `ApplePassPackager`, certificate setup (P12), images, localized resources (`.lproj/pass.strings`, localized images)
2. **Push notifications** — how to use `ApplePushNotifier`, P8 key setup, single and batch sends, handling 410 Gone responses
3. **Credentials** — `AppleCredentials` setup (P12 for signing, P8 for push, team/passType identifiers)
4. **Full example** — build a pass → package → distribute → update → push refresh
5. **Without the bundle** — standalone usage with manual serializer and HTTP client setup

#### `docs/google.md` — Google Wallet: API & Save Links

Contents:
1. **Authentication** — service account JSON, `GoogleOAuth2Authenticator`, token lifecycle
2. **CRUD operations** — `GoogleWalletClient` usage for classes and objects (create, get, update, patch)
3. **Save links** — `GoogleSaveLinkGenerator` for "Add to Google Wallet" URLs (offline, no HTTP)
4. **`createOrUpdatePass()` convenience** — single-call class + object creation
5. **Credentials** — `GoogleCredentials` setup
6. **Full example** — build a pass → create class + object → generate save link → update pass via API
7. **Without the bundle** — standalone usage

#### `docs/samsung.md` — Samsung Wallet: API & Card Lifecycle

Contents:
1. **Authentication** — partner credentials, `SamsungJwtAuthenticator`, JWT lifecycle
2. **CRUD operations** — `SamsungWalletClient` usage (create, get, update, updateCardState, pushCardUpdate)
3. **Card state management** — lifecycle transitions (active, expired, etc.)
4. **Credentials** — `SamsungCredentials` setup
5. **Full example** — build a card → create via API → update state → push update
6. **Without the bundle** — standalone usage

#### `docs/bundle.md` — Symfony Bundle: Full Integration Guide

Contents:
1. **Installation & configuration** — `wallet_kit.php` config file, per-platform credential setup, route prefix
2. **Service registration** — explicit PHP config (no autowiring), conditional loading per platform, available service IDs and aliases
3. **WalletContextFactory** — how it pre-fills infrastructure values, usage in controllers
4. **Apple Web Service** — endpoints exposed, `ApplePassProviderInterface` implementation guide, `PassRegistrationRepositoryInterface` and Doctrine default
5. **Google/Samsung callbacks** — callback endpoints, `GoogleCallbackHandlerInterface` and `SamsungCallbackHandlerInterface` implementation (optional)
6. **Throttled bulk operations** — `ThrottledPushDispatcher` (Apple), `ThrottledGoogleDispatcher`, `ThrottledSamsungDispatcher`, configuration (batchSize, batchInterval per platform), Messenger transport requirements
7. **Issuance helpers** — "Add to Wallet" URL generation for all 3 platforms
8. **Custom repository implementations** — replacing Doctrine defaults with your own
9. **Full example** — complete Symfony controller handling all 3 platforms with throttled updates
