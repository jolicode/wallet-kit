<?php

declare(strict_types=1);

use Jolicode\WalletKit\Api\Apple\ApplePassPackager;
use Jolicode\WalletKit\Api\Apple\ApplePushNotifier;
use Jolicode\WalletKit\Api\Auth\AppleApnsJwtProvider;
use Jolicode\WalletKit\Api\Credentials\AppleCredentials;
use Jolicode\WalletKit\Bundle\Apple\ApplePassProviderInterface;
use Jolicode\WalletKit\Bundle\Controller\Apple\AppleWebServiceController;
use Jolicode\WalletKit\Bundle\Repository\PassRegistrationRepositoryInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('wallet_kit.credentials.apple', AppleCredentials::class)
        ->args([
            param('wallet_kit.apple.certificate_path'),
            param('wallet_kit.apple.certificate_password'),
            param('wallet_kit.apple.wwdr_certificate_path'),
            param('wallet_kit.apple.apns_key_path'),
            param('wallet_kit.apple.apns_key_id'),
            param('wallet_kit.apple.apns_team_id'),
            param('wallet_kit.apple.team_identifier'),
            param('wallet_kit.apple.pass_type_identifier'),
        ])
    ;
    $services->alias(AppleCredentials::class, 'wallet_kit.credentials.apple');

    $services->set('wallet_kit.auth.apple_apns_jwt', AppleApnsJwtProvider::class)
        ->args([
            service('wallet_kit.credentials.apple'),
        ])
    ;
    $services->alias(AppleApnsJwtProvider::class, 'wallet_kit.auth.apple_apns_jwt');

    $services->set('wallet_kit.apple.packager', ApplePassPackager::class)
        ->args([
            service('serializer'),
            service('wallet_kit.credentials.apple'),
        ])
    ;
    $services->alias(ApplePassPackager::class, 'wallet_kit.apple.packager');

    $services->set('wallet_kit.apple.push_notifier', ApplePushNotifier::class)
        ->args([
            service('http_client'),
            service('wallet_kit.auth.apple_apns_jwt'),
            param('wallet_kit.apple.apns_sandbox'),
        ])
    ;
    $services->alias(ApplePushNotifier::class, 'wallet_kit.apple.push_notifier');

    $services->set('wallet_kit.controller.apple_web_service', AppleWebServiceController::class)
        ->args([
            service(PassRegistrationRepositoryInterface::class),
            service(ApplePassProviderInterface::class),
            service('wallet_kit.apple.packager'),
            service('logger')->nullOnInvalid(),
        ])
        ->tag('controller.service_arguments')
    ;
};
