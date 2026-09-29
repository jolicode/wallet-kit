<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Api\Credentials;

use Jolicode\WalletKit\Api\Samsung\SamsungRegionEnum;

final readonly class SamsungCredentials
{
    /**
     * @param string            $privateKeyPath path to the partner RSA private key used to sign
     *                                          Authorization JWS and card data tokens
     * @param string            $certificateId  Certificate identifier issued when the CSR/certificate
     *                                          is registered on the Samsung Partner site ("onboarding").
     *                                          Required in every Authorization JWS header.
     * @param string|null       $publicKeyPath  Optional path to Samsung's public certificate (PEM).
     *                                          Used to verify inbound notification signatures; when null,
     *                                          bundle callbacks accept requests while logging a warning.
     * @param SamsungRegionEnum $region         regional Partner API endpoint
     */
    public function __construct(
        public string $partnerId,
        public string $privateKeyPath,
        public string $certificateId,
        public ?string $publicKeyPath = null,
        public SamsungRegionEnum $region = SamsungRegionEnum::EU,
    ) {
    }
}
