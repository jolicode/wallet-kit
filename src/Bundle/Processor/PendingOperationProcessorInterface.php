<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Bundle\Processor;

use Jolicode\WalletKit\Bundle\Entity\PendingOperation;
use Jolicode\WalletKit\Bundle\WalletPlatformEnum;

interface PendingOperationProcessorInterface
{
    public function supports(): WalletPlatformEnum;

    /**
     * Processes the claimed operations and reports every operation outcome.
     *
     * Rate-limiting is exceptional: processors throw RateLimitException when the
     * platform API signals throttling, which the Messenger handler turns into a
     * delayed re-dispatch. All other per-operation problems (corrupt payload,
     * unknown operation type, failed API call) are recorded as failures.
     *
     * @param PendingOperation[] $operations
     */
    public function process(array $operations): ProcessResult;
}
