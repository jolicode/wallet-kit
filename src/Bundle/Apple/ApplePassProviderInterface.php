<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Bundle\Apple;

use Jolicode\WalletKit\Builder\BuiltWalletPass;

interface ApplePassProviderInterface
{
    public function getPass(string $passTypeIdentifier, string $serialNumber): BuiltWalletPass;

    /**
     * @return array<string, string> filename => local path or URL
     */
    public function getPassImages(string $passTypeIdentifier, string $serialNumber): array;

    /**
     * @return string[] Serial numbers updated since $since
     */
    public function getUpdatedSerialNumbers(string $passTypeIdentifier, \DateTimeInterface $since): array;

    /**
     * Returns the opaque authentication token embedded in the pass at issuance time.
     *
     * Used by the Apple Web Service endpoints to authenticate device requests
     * via the `Authorization: ApplePass <token>` header. Return null if no pass
     * exists for the given identifier/serial pair.
     */
    public function getAuthenticationToken(string $passTypeIdentifier, string $serialNumber): ?string;

    /**
     * Returns the last modification timestamp of the pass, used to honor
     * `If-Modified-Since` on getLatestPass and to populate `lastUpdated` on
     * getSerialNumbers. Return null if the pass does not exist.
     */
    public function getLastModified(string $passTypeIdentifier, string $serialNumber): ?\DateTimeImmutable;
}
