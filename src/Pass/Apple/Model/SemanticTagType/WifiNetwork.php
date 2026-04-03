<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Apple\Model\SemanticTagType;

/**
 * @phpstan-type WifiNetworkType array{ssid: string, password?: string}
 */
class WifiNetwork
{
    public function __construct(
        public string $ssid,
        public ?string $password = null,
    ) {
    }
}
