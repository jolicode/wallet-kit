<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Builder\Internal;

use Jolicode\WalletKit\Pass\Android\Model\Shared\AppLinkData;
use Jolicode\WalletKit\Pass\Android\Model\Shared\Barcode as GoogleBarcode;
use Jolicode\WalletKit\Pass\Android\Model\Shared\LinksModuleData;
use Jolicode\WalletKit\Pass\Android\Model\Shared\ReviewStatusEnum;
use Jolicode\WalletKit\Pass\Android\Model\Shared\StateEnum;
use Jolicode\WalletKit\Pass\Android\Model\Shared\TimeInterval;
use Jolicode\WalletKit\Pass\Apple\Model\Barcode;
use Jolicode\WalletKit\Pass\Apple\Model\Pass;

final class CommonWalletState
{
    /** @var list<Barcode> */
    public array $appleBarcodes = [];

    public ?GoogleBarcode $googleBarcodeOverride = null;

    public ?string $appleBackgroundColor = null;

    public ?string $googleHexBackgroundColor = null;

    public ?string $appleForegroundColor = null;

    public ?string $appleLabelColor = null;

    public ?string $groupingIdentifier = null;

    public ?int $groupingSortIndex = null;

    public ?string $webServiceURL = null;

    public ?string $authenticationToken = null;

    public ?string $appLaunchURL = null;

    /** @var list<int> */
    public array $associatedStoreIdentifiers = [];

    public ?string $appleExpirationDate = null;

    public ?bool $appleVoided = null;

    public ?TimeInterval $validTimeInterval = null;

    public ?ReviewStatusEnum $googleReviewStatus = null;

    public ?StateEnum $googleObjectState = null;

    public ?AppLinkData $appLinkData = null;

    public ?LinksModuleData $linksModuleData = null;

    /** @var callable(Pass): void|null */
    public $applePassMutator;
}
