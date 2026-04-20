<?php

declare(strict_types=1);

use Jolicode\WalletKit\Api\Auth\SamsungJwtAuthenticator;
use Jolicode\WalletKit\Api\Credentials\SamsungCredentials;
use Jolicode\WalletKit\Api\Samsung\SamsungWalletClient;
use Jolicode\WalletKit\Bundle\Controller\Samsung\SamsungCallbackController;
use Jolicode\WalletKit\Bundle\Samsung\SamsungCallbackHandlerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('wallet_kit.credentials.samsung', SamsungCredentials::class)
        ->args([
            param('wallet_kit.samsung.partner_id'),
            param('wallet_kit.samsung.private_key_path'),
            param('wallet_kit.samsung.service_id'),
        ])
    ;
    $services->alias(SamsungCredentials::class, 'wallet_kit.credentials.samsung');

    $services->set('wallet_kit.auth.samsung_jwt', SamsungJwtAuthenticator::class)
        ->args([
            service('wallet_kit.credentials.samsung'),
        ])
    ;
    $services->alias(SamsungJwtAuthenticator::class, 'wallet_kit.auth.samsung_jwt');

    $services->set('wallet_kit.samsung.client', SamsungWalletClient::class)
        ->args([
            service('http_client'),
            service('serializer'),
            service('wallet_kit.auth.samsung_jwt'),
        ])
    ;
    $services->alias(SamsungWalletClient::class, 'wallet_kit.samsung.client');

    $services->set('wallet_kit.controller.samsung_callback', SamsungCallbackController::class)
        ->args([
            service(SamsungCallbackHandlerInterface::class)->nullOnInvalid(),
        ])
        ->tag('controller.service_arguments')
    ;
};
