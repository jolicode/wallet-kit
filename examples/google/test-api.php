<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

(new Symfony\Component\Dotenv\Dotenv())->loadEnv(__DIR__ . '/.env');

use Jolicode\WalletKit\Api\Auth\GoogleOAuth2Authenticator;
use Jolicode\WalletKit\Api\Credentials\GoogleCredentials;
use Jolicode\WalletKit\Api\Google\GoogleSaveLinkGenerator;
use Jolicode\WalletKit\Api\Google\GoogleWalletClient;
use Jolicode\WalletKit\Builder\WalletPass;
use Jolicode\WalletKit\Builder\WalletPlatformContext;
use Jolicode\WalletKit\Builder\WalletSerializerFactory;
use Jolicode\WalletKit\Pass\Android\Model\Shared\Image;
use Jolicode\WalletKit\Pass\Android\Model\Shared\ImageUri;
use Jolicode\WalletKit\Pass\Android\Model\Shared\ReviewStatusEnum;
use Jolicode\WalletKit\Pass\Android\Model\Shared\StateEnum;
use Symfony\Component\HttpClient\HttpClient;

$issuerId = $_ENV['GOOGLE_WALLET_ISSUER_ID'] ?? throw new RuntimeException('Missing GOOGLE_WALLET_ISSUER_ID in .env');
$serviceAccountPath = $_ENV['GOOGLE_WALLET_SERVICE_ACCOUNT_PATH'] ?? throw new RuntimeException('Missing GOOGLE_WALLET_SERVICE_ACCOUNT_PATH in .env');

$classId = $issuerId . '.api_test_class_' . date('Ymd');
$objectId = $issuerId . '.api_test_object_' . uniqid();

$serializer = WalletSerializerFactory::create();

$context = (new WalletPlatformContext())
    ->withGoogle(
        classId: $classId,
        objectId: $objectId,
        defaultReviewStatus: ReviewStatusEnum::UNDER_REVIEW,
        defaultObjectState: StateEnum::ACTIVE,
        issuerName: 'API Test Shop',
    );

$built = WalletPass::loyalty($context, programName: 'API Test Loyalty')
    ->withAccount(accountName: 'Jane Doe', accountId: 'USER-42')
    ->build();

// Google requires a programLogo on LoyaltyClass. The cross-platform builder does not
// expose it, so we set it on the underlying model before sending.
$built->google()->issuerClass->programLogo = new Image(
    sourceUri: new ImageUri('https://www.gstatic.com/images/branding/product/1x/googleg_48dp.png'),
);

// === API client ===
$http = HttpClient::create();
$credentials = new GoogleCredentials($serviceAccountPath);
$auth = new GoogleOAuth2Authenticator($http, $credentials);
$client = new GoogleWalletClient($http, $serializer, $auth);

echo "→ Creating class and object via API...\n";

try {
    $client->createOrUpdatePass($built->google());
    echo "✓ Class and object created/updated.\n\n";
    echo "   GOOGLE_WALLET_CLASS_ID={$classId}\n";
    echo "   GOOGLE_WALLET_OBJECT_ID={$objectId}\n";
    echo "   (copy these into your .env.local to use test-update.php)\n\n";
} catch (Jolicode\WalletKit\Exception\Api\ApiResponseException $e) {
    echo '✗ API call failed: ' . $e->getMessage() . "\n";
    echo '  HTTP ' . $e->statusCode . ': ' . $e->responseBody . "\n";
    exit(1);
} catch (Throwable $e) {
    echo '✗ API call failed: ' . $e->getMessage() . "\n";
    exit(1);
}

// Generate a save link referencing just the IDs
$generator = new GoogleSaveLinkGenerator($serializer, $credentials);
$url = $generator->generateSaveLink($built->google());

echo "=== Save Link (reference) ===\n";
echo $url . "\n";
