<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

(new Symfony\Component\Dotenv\Dotenv())->loadEnv(__DIR__ . '/.env');

use Jolicode\WalletKit\Api\Apple\ApplePushNotifier;
use Jolicode\WalletKit\Api\Auth\AppleApnsJwtProvider;
use Jolicode\WalletKit\Api\Credentials\AppleCredentials;
use Symfony\Component\HttpClient\HttpClient;

$teamId = $_ENV['APPLE_TEAM_IDENTIFIER'] ?? throw new RuntimeException('Missing APPLE_TEAM_IDENTIFIER in .env');
$passTypeId = $_ENV['APPLE_PASS_TYPE_IDENTIFIER'] ?? throw new RuntimeException('Missing APPLE_PASS_TYPE_IDENTIFIER in .env');
$apnsKey = $_ENV['APPLE_APNS_KEY_PATH'] ?? throw new RuntimeException('Missing APPLE_APNS_KEY_PATH in .env');
$apnsKeyId = $_ENV['APPLE_APNS_KEY_ID'] ?? throw new RuntimeException('Missing APPLE_APNS_KEY_ID in .env');
$pushToken = $_ENV['APPLE_PUSH_TOKEN'] ?? throw new RuntimeException('Missing APPLE_PUSH_TOKEN in .env — this comes from your Web Service registration endpoint when a device adds the pass.');

$credentials = new AppleCredentials(
    certificatePath: '', // not needed for APNS
    certificatePassword: '',
    apnsKeyPath: $apnsKey,
    apnsKeyId: $apnsKeyId,
    apnsTeamId: $teamId,
    teamIdentifier: $teamId,
    passTypeIdentifier: $passTypeId,
);

$notifier = new ApplePushNotifier(
    httpClient: HttpClient::create(),
    jwtProvider: new AppleApnsJwtProvider($credentials),
    sandbox: false,
);

echo '→ Sending APNS update notification to token ' . substr($pushToken, 0, 12) . "...\n";

$response = $notifier->sendUpdateNotification($pushToken, $passTypeId);

if ($response->isSuccessful()) {
    echo "✓ APNS accepted the notification (HTTP {$response->getStatusCode()}).\n";
    echo "  The device will call back to your Web Service GET /v1/passes/{passTypeId}/{serial} for the updated .pkpass.\n";
} else {
    echo "✗ APNS rejected the notification (HTTP {$response->getStatusCode()}): {$response->getErrorReason()}\n";
    exit(1);
}
