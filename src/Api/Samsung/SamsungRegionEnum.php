<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Api\Samsung;

enum SamsungRegionEnum: string
{
    case US = 'us';
    case EU = 'eu';
    case KR = 'kr';

    public function getBaseUrl(): string
    {
        return match ($this) {
            self::US => 'https://api-us1.mpay.samsung.com/wallet/v2.1/',
            self::EU => 'https://api-eu1.mpay.samsung.com/wallet/v2.1/',
            self::KR => 'https://api-kr.mpay.samsung.com/wallet/v2.1/',
        };
    }
}
