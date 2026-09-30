<?php

declare(strict_types=1);

use Jolicode\WalletKit\Bundle\Google\ThrottledGoogleDispatcher;
use Jolicode\WalletKit\Bundle\Messenger\ProcessPendingOperationsHandler;
use Jolicode\WalletKit\Bundle\Push\ThrottledPushDispatcher;
use Jolicode\WalletKit\Bundle\Repository\DoctrinePendingOperationRepository;
use Jolicode\WalletKit\Bundle\Repository\PassRegistrationRepositoryInterface;
use Jolicode\WalletKit\Bundle\Repository\PendingOperationRepositoryInterface;
use Jolicode\WalletKit\Bundle\Samsung\ThrottledSamsungDispatcher;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    // NOTE: platform processors are registered by apple.php / google.php / samsung.php
    // (loaded only when each platform is configured). This file is loaded only when
    // Doctrine + Messenger + at least one platform are available, per WalletKitExtension.

    // Repository
    $services->set('wallet_kit.pending_operation_repository', DoctrinePendingOperationRepository::class)
        ->args([
            service('doctrine.orm.entity_manager'),
        ])
    ;
    $services->alias(PendingOperationRepositoryInterface::class, 'wallet_kit.pending_operation_repository');

    // Messenger handler
    $services->set('wallet_kit.messenger.handler.process_pending_operations', ProcessPendingOperationsHandler::class)
        ->args([
            service(PendingOperationRepositoryInterface::class),
            service('messenger.default_bus'),
            tagged_iterator('wallet_kit.pending_operation_processor'),
            param('wallet_kit.batch_config'),
            service('logger')->nullOnInvalid(),
        ])
        ->tag('messenger.message_handler')
    ;

    // Dispatchers
    $services->set('wallet_kit.push.throttled_dispatcher', ThrottledPushDispatcher::class)
        ->args([
            service('messenger.default_bus'),
            service(PendingOperationRepositoryInterface::class),
            service(PassRegistrationRepositoryInterface::class),
        ])
    ;
    $services->alias(ThrottledPushDispatcher::class, 'wallet_kit.push.throttled_dispatcher');

    $services->set('wallet_kit.google.throttled_dispatcher', ThrottledGoogleDispatcher::class)
        ->args([
            service('messenger.default_bus'),
            service(PendingOperationRepositoryInterface::class),
            service('serializer'),
        ])
    ;
    $services->alias(ThrottledGoogleDispatcher::class, 'wallet_kit.google.throttled_dispatcher');

    $services->set('wallet_kit.samsung.throttled_dispatcher', ThrottledSamsungDispatcher::class)
        ->args([
            service('messenger.default_bus'),
            service(PendingOperationRepositoryInterface::class),
            service('serializer'),
        ])
    ;
    $services->alias(ThrottledSamsungDispatcher::class, 'wallet_kit.samsung.throttled_dispatcher');
};
