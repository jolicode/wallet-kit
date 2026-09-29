<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Api\Samsung;

enum SamsungRegionEnum: string
{
    case US = 'us';
    case EU = 'eu';
    case KR = 'kr';

    /**
     * Regional Partner API host, without any API path: endpoints below build
     * full document paths ("/partner/v1/…", "/atw/v1/…", "/wltex/…") which the
     * Authorization JWS binds to (path only, excluding scheme/host/query).
     */
    public function getBaseUrl(): string
    {
        return match ($this) {
            self::US => 'https://api-us1.mpay.samsung.com',
            self::EU => 'https://api-eu1.mpay.samsung.com',
            self::KR => 'https://api-kr.mpay.samsung.com',
        };
    }

    /**
     * ISO-3166-1 alpha-2 code for the region, used as "x-smcs-cc2" header value.
     */
    public function getCountryCode(): string
    {
        return \mb_strtoupper($this->value);
    }
}
