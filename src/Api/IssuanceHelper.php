<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Api;

use Jolicode\WalletKit\Api\Google\GoogleSaveLinkGenerator;
use Jolicode\WalletKit\Builder\GoogleWalletPair;

final class IssuanceHelper
{
    public function __construct(
        private readonly ?GoogleSaveLinkGenerator $googleSaveLinkGen = null,
    ) {
    }

    /**
     * Apple: returns the URL to download the .pkpass file (your own endpoint).
     */
    public function appleAddToWalletUrl(string $passDownloadUrl): string
    {
        return $passDownloadUrl;
    }

    /**
     * Google: generates a save link with the JWT-encoded pass.
     *
     * @return string https://pay.google.com/gp/v/save/{jwt}
     */
    public function googleAddToWalletUrl(GoogleWalletPair $pair): string
    {
        if (null === $this->googleSaveLinkGen) {
            throw new \LogicException('GoogleSaveLinkGenerator is required to generate Google Add to Wallet URLs.');
        }

        return $this->googleSaveLinkGen->generateSaveLink($pair);
    }

    /**
     * Samsung: generates a deep link to add the card.
     *
     * @return string Samsung Wallet deep link URL
     */
    public function samsungAddToWalletUrl(string $cardId, string $partnerId): string
    {
        return \sprintf('https://a.]wallet.samsung.com/wallet/card?cardId=%s&partnerId=%s', urlencode($cardId), urlencode($partnerId));
    }
}
