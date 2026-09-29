<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Bundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\Messenger\MessageBusInterface;

final class WalletKitExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('wallet_kit.route_prefix', $config['route_prefix']);

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $loader->load('context.php');

        $batchConfig = [];

        if (\array_key_exists('apple', $config)) {
            $appleConfig = $config['apple'];
            $container->setParameter('wallet_kit.apple.certificate_path', $appleConfig['certificatePath']);
            $container->setParameter('wallet_kit.apple.certificate_password', $appleConfig['certificatePassword']);
            $container->setParameter('wallet_kit.apple.wwdr_certificate_path', $appleConfig['wwdrCertificatePath']);
            $container->setParameter('wallet_kit.apple.apns_key_path', $appleConfig['apnsKeyPath']);
            $container->setParameter('wallet_kit.apple.apns_key_id', $appleConfig['apnsKeyId']);
            $container->setParameter('wallet_kit.apple.apns_team_id', $appleConfig['apnsTeamId']);
            $container->setParameter('wallet_kit.apple.team_identifier', $appleConfig['teamIdentifier']);
            $container->setParameter('wallet_kit.apple.pass_type_identifier', $appleConfig['passTypeIdentifier']);
            $container->setParameter('wallet_kit.apple.apns_sandbox', $appleConfig['apnsSandbox']);

            $loader->load('apple.php');

            $batchConfig['apple'] = [
                'pushBatchSize' => $appleConfig['pushBatchSize'],
                'pushBatchInterval' => $appleConfig['pushBatchInterval'],
            ];
        }

        if (\array_key_exists('google', $config)) {
            $googleConfig = $config['google'];
            $container->setParameter('wallet_kit.google.service_account_json_path', $googleConfig['serviceAccountJsonPath']);
            $container->setParameter('wallet_kit.google.secret_token', $googleConfig['secretToken']);

            $loader->load('google.php');

            $batchConfig['google'] = [
                'apiBatchSize' => $googleConfig['apiBatchSize'],
                'apiBatchInterval' => $googleConfig['apiBatchInterval'],
            ];
        }

        if (\array_key_exists('samsung', $config)) {
            $samsungConfig = $config['samsung'];
            $container->setParameter('wallet_kit.samsung.partner_id', $samsungConfig['partnerId']);
            $container->setParameter('wallet_kit.samsung.private_key_path', $samsungConfig['privateKeyPath']);
            $container->setParameter('wallet_kit.samsung.certificate_id', $samsungConfig['certificateId']);
            $container->setParameter('wallet_kit.samsung.public_key_path', $samsungConfig['publicKeyPath']);
            $container->setParameter('wallet_kit.samsung.region', $samsungConfig['region']);

            $loader->load('samsung.php');

            $batchConfig['samsung'] = [
                'apiBatchSize' => $samsungConfig['apiBatchSize'],
                'apiBatchInterval' => $samsungConfig['apiBatchInterval'],
            ];
        }

        $container->setParameter('wallet_kit.batch_config', $batchConfig);

        // The throttling stack is only loadable when every hard dependency is present:
        // the pending-operation repository needs Doctrine, the handler needs Messenger
        // and at least one platform processor must exist (they are gated per platform).
        // NOTE: doctrine is checked through the DoctrineBundle class, because the
        // bare EntityManagerInterface also exists when only doctrine/orm is installed
        // (the "service doctrine.orm.entity_manager" would then be missing at runtime).
        $anyPlatform = [] !== $batchConfig;
        $messengerAvailable = ContainerBuilder::willBeAvailable('symfony/messenger', MessageBusInterface::class, ['jolicode/wallet-kit']);

        // NOTE: doctrine is checked through the DoctrineBundle class, because the
        // bare EntityManagerInterface also exists when only doctrine/orm is installed
        // (the "service doctrine.orm.entity_manager" would then be missing at runtime).
        // Passing the class by string keeps static analysis happy on installs
        // where doctrine/doctrine-bundle is not a dependency (dev/test contexts).
        $doctrineBundleClass = 'Doctrine\\Bundle\\DoctrineBundle\\DoctrineBundle';
        $doctrineAvailable = ContainerBuilder::willBeAvailable('doctrine/doctrine-bundle', $doctrineBundleClass, ['jolicode/wallet-kit']);

        if ($anyPlatform && $messengerAvailable && $doctrineAvailable) {
            $loader->load('throttling.php');
        }
    }
}
