<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Bundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Classic Bundle so Symfony auto-discovers {@see WalletKitExtension} in the
 * DependencyInjection namespace and processes the "wallet_kit" config tree.
 *
 * Routes are NOT auto-registered (Symfony never calls loadRoutes() on bundles):
 * apps import the platform route files they need, see docs/bundle.md.
 */
final class WalletKitBundle extends Bundle
{
}
