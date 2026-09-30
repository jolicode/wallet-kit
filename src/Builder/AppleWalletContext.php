<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Builder;

/**
 * Apple Wallet identifiers and defaults for builder output.
 */
final class AppleWalletContext
{
    public function __construct(
        public readonly string $teamIdentifier,
        public readonly string $passTypeIdentifier,
        public readonly string $serialNumber,
        public readonly string $organizationName,
        public readonly string $description,
        public readonly int $formatVersion = 1,
        public readonly ?string $webServiceURL = null,
        public readonly ?string $authenticationToken = null,
    ) {
    }
}
