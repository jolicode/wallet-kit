<?php
declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

(new \Symfony\Component\Dotenv\Dotenv())->loadEnv(__DIR__ . '/.env');

use Jolicode\WalletKit\Api\Credentials\GoogleCredentials;
use Jolicode\WalletKit\Api\Google\GoogleSaveLinkGenerator;
use Jolicode\WalletKit\Builder\WalletPass;
use Jolicode\WalletKit\Builder\WalletPlatformContext;
use Jolicode\WalletKit\Common\Color;
use Jolicode\WalletKit\Pass\Android\Model\Offer\RedemptionChannelEnum;
use Jolicode\WalletKit\Pass\Android\Model\Shared\ReviewStatusEnum;
use Jolicode\WalletKit\Pass\Android\Model\Shared\StateEnum;

// === Config (from .env) ===
$issuerId           = $_ENV['GOOGLE_WALLET_ISSUER_ID'] ?? throw new \RuntimeException('Missing GOOGLE_WALLET_ISSUER_ID in .env');
$serviceAccountPath = $_ENV['GOOGLE_WALLET_SERVICE_ACCOUNT_PATH'] ?? throw new \RuntimeException('Missing GOOGLE_WALLET_SERVICE_ACCOUNT_PATH in .env');

// Unique IDs for this test
$classSuffix  = 'offer_class_' . date('Ymd_His');
$objectSuffix = 'offer_object_' . uniqid();
$classId  = $issuerId . '.' . $classSuffix;
$objectId = $issuerId . '.' . $objectSuffix;

// === Serializer ===
// Use the library's test serializer factory (easier than wiring everything manually)
$serializer = \Jolicode\WalletKit\Tests\Builder\BuilderTestSerializerFactory::create();

// === Build the pass ===
$context = (new WalletPlatformContext())
    ->withGoogle(
        classId: $classId,
        objectId: $objectId,
        defaultReviewStatus: ReviewStatusEnum::UNDER_REVIEW,
        defaultObjectState: StateEnum::ACTIVE,
        issuerName: 'WalletKit Test Shop',
    );

$built = WalletPass::offer(
    $context,
    title: '15% off spring sale',
    provider: 'WalletKit Test Shop',
    redemptionChannel: RedemptionChannelEnum::BOTH,
)
    ->withBackgroundColor(Color::fromRgbString('rgb(200, 50, 80)'))
    ->build();

// === Generate save link ===
$credentials = new GoogleCredentials($serviceAccountPath);
$generator   = new GoogleSaveLinkGenerator($serializer, $credentials);

$url = $generator->generateSaveLink($built->google());

echo "\n=== Generated Save Link ===\n";
echo $url . "\n\n";
echo "Length: " . strlen($url) . " characters\n";
echo "Open this URL on your Android device with Google Wallet installed.\n";
