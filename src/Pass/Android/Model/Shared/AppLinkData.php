<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Shared;

/**
 * @phpstan-import-type AppLinkInfoType from AppLinkInfo
 *
 * @phpstan-type AppLinkDataType array{androidAppLinkInfo?: AppLinkInfoType, webAppLinkInfo?: AppLinkInfoType}
 */
class AppLinkData
{
    public function __construct(
        public ?AppLinkInfo $androidAppLinkInfo = null,
        public ?AppLinkInfo $webAppLinkInfo = null,
    ) {
    }
}
