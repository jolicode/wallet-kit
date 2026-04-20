<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Bundle\Messenger;

use Jolicode\WalletKit\Bundle\WalletPlatformEnum;

final class ProcessPendingOperationsMessage
{
    public function __construct(
        public readonly WalletPlatformEnum $platform,
        public readonly string $batchGroupId,
    ) {
    }
}
