<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Builder;

final class SamsungWalletContext
{
    public function __construct(
        public readonly string $refId,
        public readonly string $language = 'en',
        public readonly ?string $appLinkLogo = null,
        public readonly ?string $appLinkName = null,
        public readonly ?string $appLinkData = null,
    ) {
    }
}
