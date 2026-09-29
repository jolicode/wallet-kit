<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Api;

use Jolicode\WalletKit\Api\Google\GoogleSaveLinkGenerator;
use Jolicode\WalletKit\Builder\GoogleWalletPair;
use Jolicode\WalletKit\Exception\Api\MissingServiceException;

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
        if (!\str_starts_with($passDownloadUrl, 'https://')) {
            throw new \ValueError('Apple pass download URLs must be served over HTTPS.');
        }

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
            throw new MissingServiceException('GoogleSaveLinkGenerator is required to generate Google Add to Wallet URLs.');
        }

        return $this->googleSaveLinkGen->generateSaveLink($pair);
    }

    /**
     * Samsung: "Data Transmit Link" — opens the Add to Samsung Wallet page with the
     * card payload in the "cdata" query token.
     *
     * @param string $cardId     Card identifier from the Samsung Partner site.
     * @param string $cdataToken Signed card payload, produced by SamsungCardTokenizer
     *                           (base64url — already query-safe).
     */
    public function samsungAddToWalletUrl(string $cardId, string $cdataToken): string
    {
        return \sprintf('https://a.wallet.samsung.com/wallet/card/%s?cdata=%s', rawurlencode($cardId), $cdataToken);
    }
}
