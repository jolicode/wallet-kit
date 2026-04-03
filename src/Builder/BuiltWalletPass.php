<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Builder;

use Jolicode\WalletKit\Pass\Apple\Model\Pass;

final class BuiltWalletPass
{
    public function __construct(
        private readonly Pass $apple,
        private readonly GoogleWalletPair $google,
    ) {
    }

    public function apple(): Pass
    {
        return $this->apple;
    }

    public function google(): GoogleWalletPair
    {
        return $this->google;
    }

    public function googleVertical(): GoogleVerticalEnum
    {
        return $this->google->vertical;
    }
}
