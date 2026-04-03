<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Builder;

use Jolicode\WalletKit\Builder\Internal\CommonWalletState;

abstract class AbstractWalletBuilder
{
    use CommonWalletBuilderTrait;

    protected CommonWalletState $common;

    public function __construct(
        protected readonly WalletPlatformContext $context,
    ) {
        $this->common = new CommonWalletState();
    }

    abstract public function build(): BuiltWalletPass;
}
