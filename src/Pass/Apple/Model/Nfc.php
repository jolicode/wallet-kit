<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Apple\Model;

/**
 * @phpstan-type NfcType array{message: string, encryptionPublicKey: string, requiresAuthentication?: bool}
 */
class Nfc
{
    public function __construct(
        public string $message,
        public string $encryptionPublicKey,
        public ?bool $requiresAuthentication = null,
    ) {
    }
}
