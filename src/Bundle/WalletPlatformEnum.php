<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Bundle;

enum WalletPlatformEnum: string
{
    case APPLE = 'apple';
    case GOOGLE = 'google';
    case SAMSUNG = 'samsung';
}
