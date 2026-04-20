<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Bundle\Processor;

use Jolicode\WalletKit\Bundle\Entity\PendingOperation;
use Jolicode\WalletKit\Bundle\WalletPlatformEnum;

interface PendingOperationProcessorInterface
{
    public function supports(): WalletPlatformEnum;

    /**
     * @param PendingOperation[] $operations
     */
    public function process(array $operations): void;
}
