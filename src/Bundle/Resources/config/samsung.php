<?php

declare(strict_types=1);

use Jolicode\WalletKit\Api\Auth\SamsungRequestAuthenticator;
use Jolicode\WalletKit\Api\Credentials\SamsungCredentials;
use Jolicode\WalletKit\Api\Samsung\SamsungRegionEnum;
use Jolicode\WalletKit\Api\Samsung\SamsungWalletClient;
use Jolicode\WalletKit\Bundle\Controller\Samsung\SamsungCallbackController;
use Jolicode\WalletKit\Bundle\Processor\SamsungApiProcessor;
use Jolicode\WalletKit\Bundle\Samsung\SamsungCallbackHandlerInterface;
use Jolicode\WalletKit\Bundle\Samsung\SamsungNotificationVerifier;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\inline_service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('wallet_kit.credentials.samsung', SamsungCredentials::class)
        ->args([
            param('wallet_kit.samsung.partner_id'),
            param('wallet_kit.samsung.private_key_path'),
            param('wallet_kit.samsung.certificate_id'),
            param('wallet_kit.samsung.public_key_path'),
            inline_service(SamsungRegionEnum::class)
                ->factory([SamsungRegionEnum::class, 'from'])
                ->args([param('wallet_kit.samsung.region')]),
        ])
    ;
    $services->alias(SamsungCredentials::class, 'wallet_kit.credentials.samsung');

    $services->set('wallet_kit.auth.samsung_request', SamsungRequestAuthenticator::class)
        ->args([
            service('wallet_kit.credentials.samsung'),
        ])
    ;
    $services->alias(SamsungRequestAuthenticator::class, 'wallet_kit.auth.samsung_request');

    $services->set('wallet_kit.samsung.client', SamsungWalletClient::class)
        ->args([
            service('http_client'),
            service('serializer'),
            service('wallet_kit.auth.samsung_request'),
            service('wallet_kit.credentials.samsung'),
        ])
    ;
    $services->alias(SamsungWalletClient::class, 'wallet_kit.samsung.client');

    $services->set('wallet_kit.processor.samsung_api', SamsungApiProcessor::class)
        ->args([
            service(SamsungWalletClient::class),
            service('serializer'),
            service('logger')->nullOnInvalid(),
        ])
        ->tag('wallet_kit.pending_operation_processor')
    ;

    $services->set('wallet_kit.samsung.notification_verifier', SamsungNotificationVerifier::class)
        ->args([
            param('wallet_kit.samsung.public_key_path'),
            service('logger')->nullOnInvalid(),
        ])
    ;

    $services->set('wallet_kit.controller.samsung_callback', SamsungCallbackController::class)
        ->args([
            service(SamsungCallbackHandlerInterface::class)->nullOnInvalid(),
            service('wallet_kit.samsung.notification_verifier'),
        ])
        ->tag('controller.service_arguments')
    ;
};
