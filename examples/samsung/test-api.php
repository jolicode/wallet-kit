<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

(new Symfony\Component\Dotenv\Dotenv())->loadEnv(__DIR__ . '/.env');

use Jolicode\WalletKit\Api\Auth\SamsungJwtAuthenticator;
use Jolicode\WalletKit\Api\Credentials\SamsungCredentials;
use Jolicode\WalletKit\Api\IssuanceHelper;
use Jolicode\WalletKit\Api\Samsung\SamsungRegionEnum;
use Jolicode\WalletKit\Api\Samsung\SamsungWalletClient;
use Jolicode\WalletKit\Builder\WalletPass;
use Jolicode\WalletKit\Builder\WalletPlatformContext;
use Jolicode\WalletKit\Builder\WalletSerializerFactory;
use Jolicode\WalletKit\Common\Color;
use Jolicode\WalletKit\Pass\Android\Model\Offer\RedemptionChannelEnum;
use Symfony\Component\HttpClient\HttpClient;

$partnerId = $_ENV['SAMSUNG_PARTNER_ID'] ?? throw new RuntimeException('Missing SAMSUNG_PARTNER_ID in .env');
$privateKeyPath = $_ENV['SAMSUNG_PRIVATE_KEY_PATH'] ?? throw new RuntimeException('Missing SAMSUNG_PRIVATE_KEY_PATH in .env');
$serviceId = $_ENV['SAMSUNG_SERVICE_ID'] ?? null;
$regionName = $_ENV['SAMSUNG_REGION'] ?? 'EU';

if (!is_file($privateKeyPath)) {
    throw new RuntimeException(sprintf('Private key not found at "%s".', $privateKeyPath));
}

$region = SamsungRegionEnum::tryFrom(strtolower($regionName)) ?? SamsungRegionEnum::EU;

$refId = 'samsung-offer-' . date('Ymd-His');

// === Build the pass ===
$context = (new WalletPlatformContext())
    ->withSamsung(refId: $refId, language: 'en');

$built = WalletPass::offer(
    $context,
    title: '15% off spring sale',
    provider: 'WalletKit Test Shop',
    redemptionChannel: RedemptionChannelEnum::BOTH,
)
    ->withBackgroundColor(Color::fromRgbString('rgb(200, 50, 80)'))
    ->build();

// === API client ===
$credentials = new SamsungCredentials(
    partnerId: $partnerId,
    privateKeyPath: $privateKeyPath,
    serviceId: '' === $serviceId ? null : $serviceId,
    region: $region,
);
$serializer = WalletSerializerFactory::create();
$authenticator = new SamsungJwtAuthenticator($credentials);
$client = new SamsungWalletClient(HttpClient::create(), $serializer, $authenticator, $credentials);

echo "→ Creating Samsung card in region {$region->name}...\n";

$response = $client->createCard($built->samsung());

if (!$response->isSuccessful()) {
    echo "✗ API call failed: HTTP {$response->getStatusCode()}\n";
    echo "  Body: {$response->getRawBody()}\n";
    exit(1);
}

$cardId = $response->getData()['cardId'] ?? null;

if (!is_string($cardId) || '' === $cardId) {
    echo "✗ API returned HTTP {$response->getStatusCode()} but no cardId was found in the body:\n";
    echo "  {$response->getRawBody()}\n";
    exit(1);
}

echo "✓ Card created (cardId: {$cardId}).\n\n";
echo "   SAMSUNG_CARD_ID={$cardId}\n";
echo "   (copy this into your .env.local to use test-push.php)\n\n";

// === Add-to-Wallet URL ===
$helper = new IssuanceHelper();
$url = $helper->samsungAddToWalletUrl($cardId, $partnerId);

echo "=== Add-to-Wallet URL ===\n";
echo $url . "\n";
