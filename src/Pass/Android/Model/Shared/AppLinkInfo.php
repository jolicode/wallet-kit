<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Shared;

/**
 * @phpstan-import-type ImageType from Image
 * @phpstan-import-type LocalizedStringType from LocalizedString
 * @phpstan-import-type AppTargetType from AppTarget
 *
 * @phpstan-type AppLinkInfoType array{appLogoImage?: ImageType, title?: LocalizedStringType, description?: LocalizedStringType, appTarget?: AppTargetType}
 */
class AppLinkInfo
{
    public function __construct(
        public ?Image $appLogoImage = null,
        public ?LocalizedString $title = null,
        public ?LocalizedString $description = null,
        public ?AppTarget $appTarget = null,
    ) {
    }
}
