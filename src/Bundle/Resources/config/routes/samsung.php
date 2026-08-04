<?php

declare(strict_types=1);

use Jolicode\WalletKit\Bundle\Controller\Samsung\SamsungCallbackController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $prefix = '%wallet_kit.route_prefix%';

    $routes->add('wallet_kit_samsung_callback', $prefix . '/samsung/callback')
        ->controller([SamsungCallbackController::class, 'handleCallback'])
        ->methods(['POST'])
    ;
};
