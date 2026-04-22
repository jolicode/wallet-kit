<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

(new Symfony\Component\Dotenv\Dotenv())->loadEnv(__DIR__ . '/.env');

use Jolicode\WalletKit\Api\Auth\SamsungJwtAuthenticator;
use Jolicode\WalletKit\Api\Credentials\SamsungCredentials;
use Jolicode\WalletKit\Api\Samsung\SamsungRegionEnum;
use Jolicode\WalletKit\Api\Samsung\SamsungWalletClient;
use Jolicode\WalletKit\Builder\WalletSerializerFactory;
use Symfony\Component\HttpClient\HttpClient;

$partnerId = $_ENV['SAMSUNG_PARTNER_ID'] ?? throw new RuntimeException('Missing SAMSUNG_PARTNER_ID in .env');
$privateKeyPath = $_ENV['SAMSUNG_PRIVATE_KEY_PATH'] ?? throw new RuntimeException('Missing SAMSUNG_PRIVATE_KEY_PATH in .env');
$serviceId = $_ENV['SAMSUNG_SERVICE_ID'] ?? null;
$regionName = $_ENV['SAMSUNG_REGION'] ?? 'EU';
$cardId = $_ENV['SAMSUNG_CARD_ID'] ?? throw new RuntimeException('Missing SAMSUNG_CARD_ID in .env (use the value printed by test-api.php)');

$region = SamsungRegionEnum::tryFrom(strtolower($regionName)) ?? SamsungRegionEnum::EU;

$credentials = new SamsungCredentials(
    partnerId: $partnerId,
    privateKeyPath: $privateKeyPath,
    serviceId: '' === $serviceId ? null : $serviceId,
    region: $region,
);
$client = new SamsungWalletClient(
    HttpClient::create(),
    WalletSerializerFactory::create(),
    new SamsungJwtAuthenticator($credentials),
    $credentials,
);

echo "→ Triggering Samsung Wallet push update for card {$cardId}...\n";

$response = $client->pushCardUpdate($cardId);

if ($response->isSuccessful()) {
    echo "✓ Push update accepted (HTTP {$response->getStatusCode()}).\n";
    echo "  Open Samsung Wallet on your Galaxy device and pull down to refresh — the card should pick up any server-side changes.\n";
} else {
    echo "✗ Push update rejected (HTTP {$response->getStatusCode()}):\n";
    echo "  {$response->getRawBody()}\n";
    exit(1);
}
