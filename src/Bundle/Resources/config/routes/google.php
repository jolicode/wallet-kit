<?php

declare(strict_types=1);

use Jolicode\WalletKit\Bundle\Controller\Google\GoogleCallbackController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $prefix = '%wallet_kit.route_prefix%';

    $routes->add('wallet_kit_google_callback', $prefix . '/google/callback')
        ->controller([GoogleCallbackController::class, 'handleCallback'])
        ->methods(['POST'])
    ;
};
