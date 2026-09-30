<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Api\Credentials;

final readonly class AppleCredentials
{
    public function __construct(
        /** Path to the P12 certificate for .pkpass signing */
        public string $certificatePath,
        public string $certificatePassword,
        /** Path to WWDR intermediate CA (null = built-in AppleWWDRCAG4.cer) */
        public ?string $wwdrCertificatePath = null,
        /** Path to P8 key for APNS push (optional, only needed for push) */
        public ?string $apnsKeyPath = null,
        public ?string $apnsKeyId = null,
        public ?string $apnsTeamId = null,
        public ?string $teamIdentifier = null,
        public ?string $passTypeIdentifier = null,
    ) {
    }
}
