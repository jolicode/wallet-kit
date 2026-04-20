<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Bundle;

use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

final class WalletKitBundle extends AbstractBundle
{
    public function loadRoutes(RoutingConfigurator $routes): void
    {
        $routesDir = __DIR__ . '/Resources/config/routes/';

        if ($this->hasAppleConfig()) {
            $routes->import($routesDir . 'apple.php');
        }

        if ($this->hasGoogleConfig()) {
            $routes->import($routesDir . 'google.php');
        }

        if ($this->hasSamsungConfig()) {
            $routes->import($routesDir . 'samsung.php');
        }
    }

    private function hasAppleConfig(): bool
    {
        return $this->container->hasParameter('wallet_kit.apple.certificate_path');
    }

    private function hasGoogleConfig(): bool
    {
        return $this->container->hasParameter('wallet_kit.google.service_account_json_path');
    }

    private function hasSamsungConfig(): bool
    {
        return $this->container->hasParameter('wallet_kit.samsung.partner_id');
    }
}
