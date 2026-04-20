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
}
