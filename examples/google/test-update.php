<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

(new Symfony\Component\Dotenv\Dotenv())->loadEnv(__DIR__ . '/.env');

use Jolicode\WalletKit\Api\Auth\GoogleOAuth2Authenticator;
use Jolicode\WalletKit\Api\Credentials\GoogleCredentials;
use Jolicode\WalletKit\Api\Google\GoogleWalletClient;
use Jolicode\WalletKit\Builder\WalletPass;
use Jolicode\WalletKit\Builder\WalletPlatformContext;
use Jolicode\WalletKit\Builder\WalletSerializerFactory;
use Jolicode\WalletKit\Pass\Android\Model\Shared\ReviewStatusEnum;
use Jolicode\WalletKit\Pass\Android\Model\Shared\StateEnum;
use Symfony\Component\HttpClient\HttpClient;

$issuerId = $_ENV['GOOGLE_WALLET_ISSUER_ID'] ?? throw new RuntimeException('Missing GOOGLE_WALLET_ISSUER_ID in .env');
$serviceAccountPath = $_ENV['GOOGLE_WALLET_SERVICE_ACCOUNT_PATH'] ?? throw new RuntimeException('Missing GOOGLE_WALLET_SERVICE_ACCOUNT_PATH in .env');
$classId = $_ENV['GOOGLE_WALLET_CLASS_ID'] ?? throw new RuntimeException('Missing GOOGLE_WALLET_CLASS_ID in .env (use the one printed by test-api.php)');
$objectId = $_ENV['GOOGLE_WALLET_OBJECT_ID'] ?? throw new RuntimeException('Missing GOOGLE_WALLET_OBJECT_ID in .env (use the one printed by test-api.php)');

$serializer = WalletSerializerFactory::create();

$context = (new WalletPlatformContext())
    ->withGoogle(
        classId: $classId,
        objectId: $objectId,
        defaultReviewStatus: ReviewStatusEnum::UNDER_REVIEW,
        defaultObjectState: StateEnum::ACTIVE,
        issuerName: 'API Test Shop',
    );

// Same pass with different values so you can observe the change
$built = WalletPass::loyalty($context, programName: 'UPDATED Loyalty ' . date('H:i:s'))
    ->withAccount(accountName: 'Jane Doe (updated)', accountId: 'USER-42')
    ->build();

$http = HttpClient::create();
$auth = new GoogleOAuth2Authenticator($http, new GoogleCredentials($serviceAccountPath));
$client = new GoogleWalletClient($http, $serializer, $auth);

$response = $client->updateObject($built->google());

if ($response->isSuccessful()) {
    echo "✓ Object updated. Refresh Google Wallet on your phone.\n";
} else {
    echo '✗ Update failed: HTTP ' . $response->getStatusCode() . "\n";
    print_r($response->getData());
}
