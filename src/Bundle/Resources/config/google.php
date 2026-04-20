<?php

declare(strict_types=1);

use Jolicode\WalletKit\Api\Auth\GoogleOAuth2Authenticator;
use Jolicode\WalletKit\Api\Credentials\GoogleCredentials;
use Jolicode\WalletKit\Api\Google\GoogleSaveLinkGenerator;
use Jolicode\WalletKit\Api\Google\GoogleWalletClient;
use Jolicode\WalletKit\Bundle\Controller\Google\GoogleCallbackController;
use Jolicode\WalletKit\Bundle\Google\GoogleCallbackHandlerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('wallet_kit.credentials.google', GoogleCredentials::class)
        ->args([
            param('wallet_kit.google.service_account_json_path'),
        ])
    ;
    $services->alias(GoogleCredentials::class, 'wallet_kit.credentials.google');

    $services->set('wallet_kit.auth.google_oauth2', GoogleOAuth2Authenticator::class)
        ->args([
            service('http_client'),
            service('wallet_kit.credentials.google'),
        ])
    ;
    $services->alias(GoogleOAuth2Authenticator::class, 'wallet_kit.auth.google_oauth2');

    $services->set('wallet_kit.google.client', GoogleWalletClient::class)
        ->args([
            service('http_client'),
            service('serializer'),
            service('wallet_kit.auth.google_oauth2'),
        ])
    ;
    $services->alias(GoogleWalletClient::class, 'wallet_kit.google.client');

    $services->set('wallet_kit.google.save_link_generator', GoogleSaveLinkGenerator::class)
        ->args([
            service('serializer'),
            service('wallet_kit.credentials.google'),
        ])
    ;
    $services->alias(GoogleSaveLinkGenerator::class, 'wallet_kit.google.save_link_generator');

    $services->set('wallet_kit.controller.google_callback', GoogleCallbackController::class)
        ->args([
            service(GoogleCallbackHandlerInterface::class)->nullOnInvalid(),
        ])
        ->tag('controller.service_arguments')
    ;
};
