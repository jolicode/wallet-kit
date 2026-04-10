<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Builder;

use Jolicode\WalletKit\Exception\ApplePassNotAvailableException;
use Jolicode\WalletKit\Exception\GoogleWalletPairNotAvailableException;
use Jolicode\WalletKit\Exception\SamsungCardNotAvailableException;
use Jolicode\WalletKit\Pass\Apple\Model\Pass;
use Jolicode\WalletKit\Pass\Samsung\Model\Card;

final class BuiltWalletPass
{
    public function __construct(
        private readonly ?Pass $apple,
        private readonly ?GoogleWalletPair $google,
        private readonly ?Card $samsung = null,
    ) {
    }

    public function apple(): Pass
    {
        if (null === $this->apple) {
            throw new ApplePassNotAvailableException();
        }

        return $this->apple;
    }

    public function google(): GoogleWalletPair
    {
        if (null === $this->google) {
            throw new GoogleWalletPairNotAvailableException();
        }

        return $this->google;
    }

    public function googleVertical(): GoogleVerticalEnum
    {
        return $this->google()->vertical;
    }

    public function samsung(): Card
    {
        if (null === $this->samsung) {
            throw new SamsungCardNotAvailableException();
        }

        return $this->samsung;
    }
}
