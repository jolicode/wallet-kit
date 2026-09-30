<?php

declare(strict_types=1);

use Jolicode\WalletKit\Bundle\Controller\Apple\AppleWebServiceController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $prefix = '%wallet_kit.route_prefix%';

    $routes->add('wallet_kit_apple_register_device', $prefix . '/apple/v1/devices/{deviceId}/registrations/{passTypeId}/{serialNumber}')
        ->controller([AppleWebServiceController::class, 'registerDevice'])
        ->methods(['POST'])
    ;

    $routes->add('wallet_kit_apple_unregister_device', $prefix . '/apple/v1/devices/{deviceId}/registrations/{passTypeId}/{serialNumber}')
        ->controller([AppleWebServiceController::class, 'unregisterDevice'])
        ->methods(['DELETE'])
    ;

    $routes->add('wallet_kit_apple_serial_numbers', $prefix . '/apple/v1/devices/{deviceId}/registrations/{passTypeId}')
        ->controller([AppleWebServiceController::class, 'getSerialNumbers'])
        ->methods(['GET'])
    ;

    $routes->add('wallet_kit_apple_latest_pass', $prefix . '/apple/v1/passes/{passTypeId}/{serialNumber}')
        ->controller([AppleWebServiceController::class, 'getLatestPass'])
        ->methods(['GET'])
    ;

    $routes->add('wallet_kit_apple_log', $prefix . '/apple/v1/log')
        ->controller([AppleWebServiceController::class, 'log'])
        ->methods(['POST'])
    ;
};
