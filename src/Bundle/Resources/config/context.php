<?php

declare(strict_types=1);

use Jolicode\WalletKit\Api\Credentials\AppleCredentials;
use Jolicode\WalletKit\Api\Credentials\GoogleCredentials;
use Jolicode\WalletKit\Api\Credentials\SamsungCredentials;
use Jolicode\WalletKit\Bundle\WalletContextFactory;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('wallet_kit.context_factory', WalletContextFactory::class)
        ->args([
            service('router'),
            service(AppleCredentials::class)->nullOnInvalid(),
            service(GoogleCredentials::class)->nullOnInvalid(),
            service(SamsungCredentials::class)->nullOnInvalid(),
            param('wallet_kit.route_prefix'),
        ])
    ;

    $services->alias(WalletContextFactory::class, 'wallet_kit.context_factory');
};
