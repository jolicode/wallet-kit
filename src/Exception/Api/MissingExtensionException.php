<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Exception\Api;

use Jolicode\WalletKit\Exception\WalletKitException;

final class MissingExtensionException extends \LogicException implements WalletKitException
{
}
