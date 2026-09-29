<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Exception\Api;

use Jolicode\WalletKit\Exception\WalletKitException;

/**
 * Thrown when an optional dependency of a helper is missing (e.g. calling
 * IssuanceHelper::googleAddToWalletUrl without a GoogleSaveLinkGenerator).
 */
final class MissingServiceException extends \LogicException implements WalletKitException
{
}
