<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

(new Symfony\Component\Dotenv\Dotenv())->loadEnv(__DIR__ . '/.env');

use Jolicode\WalletKit\Api\Apple\ApplePassPackager;
use Jolicode\WalletKit\Api\Credentials\AppleCredentials;
use Jolicode\WalletKit\Builder\WalletPass;
use Jolicode\WalletKit\Builder\WalletPlatformContext;
use Jolicode\WalletKit\Builder\WalletSerializerFactory;
use Jolicode\WalletKit\Common\Color;
use Jolicode\WalletKit\Pass\Android\Model\Offer\RedemptionChannelEnum;

$teamId = $_ENV['APPLE_TEAM_IDENTIFIER'] ?? throw new RuntimeException('Missing APPLE_TEAM_IDENTIFIER in .env');
$passTypeId = $_ENV['APPLE_PASS_TYPE_IDENTIFIER'] ?? throw new RuntimeException('Missing APPLE_PASS_TYPE_IDENTIFIER in .env');
$certPath = $_ENV['APPLE_CERTIFICATE_PATH'] ?? throw new RuntimeException('Missing APPLE_CERTIFICATE_PATH in .env');
$certPassword = $_ENV['APPLE_CERTIFICATE_PASSWORD'] ?? '';
$iconPath = $_ENV['APPLE_ICON_PATH'] ?? throw new RuntimeException('Missing APPLE_ICON_PATH in .env');

if (!is_file($certPath)) {
    throw new RuntimeException(sprintf('Certificate not found at "%s".', $certPath));
}

if (!is_file($iconPath)) {
    throw new RuntimeException(sprintf('Icon not found at "%s". Provide a PNG (29x29 min) via APPLE_ICON_PATH.', $iconPath));
}

$serialNumber = 'sample-' . date('Ymd-His');

// === Build the pass ===
$context = (new WalletPlatformContext())
    ->withApple(
        teamIdentifier: $teamId,
        passTypeIdentifier: $passTypeId,
        serialNumber: $serialNumber,
        organizationName: 'WalletKit Test Shop',
        description: '15% off spring sale',
    );

$built = WalletPass::offer(
    $context,
    title: '15% off spring sale',
    provider: 'WalletKit Test Shop',
    redemptionChannel: RedemptionChannelEnum::BOTH,
)
    ->withBackgroundColor(Color::fromRgbString('rgb(200, 50, 80)'))
    ->build();

// === Package + sign ===
$serializer = WalletSerializerFactory::create();
$credentials = new AppleCredentials(
    certificatePath: $certPath,
    certificatePassword: $certPassword,
    teamIdentifier: $teamId,
    passTypeIdentifier: $passTypeId,
);
$packager = new ApplePassPackager($serializer, $credentials);

$pkpassBinary = $packager->package(
    $built->apple(),
    images: ['icon.png' => $iconPath],
);

// === Write to disk ===
$outputPath = __DIR__ . '/sample.pkpass';
file_put_contents($outputPath, $pkpassBinary);

echo '✓ Wrote ' . number_format(strlen($pkpassBinary)) . " bytes to:\n";
echo "   {$outputPath}\n\n";
echo "Transfer to an iPhone (AirDrop, email, or serve over HTTPS) and tap to open.\n";
echo "iOS will show the 'Add to Apple Wallet' prompt if the signature is valid.\n";
