<?php

declare(strict_types=1);

use Jolicode\WalletKit\Api\Apple\ApplePushNotifier;
use Jolicode\WalletKit\Api\Google\GoogleWalletClient;
use Jolicode\WalletKit\Api\Samsung\SamsungWalletClient;
use Jolicode\WalletKit\Bundle\Google\ThrottledGoogleDispatcher;
use Jolicode\WalletKit\Bundle\Messenger\ProcessPendingOperationsHandler;
use Jolicode\WalletKit\Bundle\Processor\ApplePushProcessor;
use Jolicode\WalletKit\Bundle\Processor\GoogleApiProcessor;
use Jolicode\WalletKit\Bundle\Processor\SamsungApiProcessor;
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

    // Repository
    $services->set('wallet_kit.pending_operation_repository', DoctrinePendingOperationRepository::class)
        ->args([
            service('doctrine.orm.entity_manager'),
        ])
    ;
    $services->alias(PendingOperationRepositoryInterface::class, 'wallet_kit.pending_operation_repository');

    // Processors
    $services->set('wallet_kit.processor.apple_push', ApplePushProcessor::class)
        ->args([
            service(ApplePushNotifier::class),
            service(PassRegistrationRepositoryInterface::class),
        ])
        ->tag('wallet_kit.pending_operation_processor')
    ;

    $services->set('wallet_kit.processor.google_api', GoogleApiProcessor::class)
        ->args([
            service(GoogleWalletClient::class),
            service('serializer'),
        ])
        ->tag('wallet_kit.pending_operation_processor')
    ;

    $services->set('wallet_kit.processor.samsung_api', SamsungApiProcessor::class)
        ->args([
            service(SamsungWalletClient::class),
            service('serializer'),
        ])
        ->tag('wallet_kit.pending_operation_processor')
    ;

    // Messenger handler
    $services->set('wallet_kit.messenger.handler.process_pending_operations', ProcessPendingOperationsHandler::class)
        ->args([
            service(PendingOperationRepositoryInterface::class),
            service('messenger.default_bus'),
            tagged_iterator('wallet_kit.pending_operation_processor'),
            param('wallet_kit.batch_config'),
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
