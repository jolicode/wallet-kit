<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Tests\Bundle\DependencyInjection;

use Jolicode\WalletKit\Bundle\DependencyInjection\WalletKitExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class WalletKitExtensionTest extends TestCase
{
    /**
     * Builds a container through the extension, stubbing the shared framework
     * services the config files reference (http_client, router, serializer…)
     * so the container can be compiled without a full Symfony app.
     */
    private function createProcessedContainer(array $config = []): ContainerBuilder
    {
        $extension = new WalletKitExtension();
        $container = new ContainerBuilder();

        foreach (['http_client', 'router', 'serializer', 'logger'] as $serviceId) {
            $container->register($serviceId, \stdClass::class);
        }

        $extension->load([$config], $container);

        return $container;
    }

    public function testEmptyConfigSetsDefaultsAndNoPlatformServices(): void
    {
        $container = $this->createProcessedContainer();

        self::assertSame('/wallet-kit', $container->getParameter('wallet_kit.route_prefix'));
        self::assertSame([], $container->getParameter('wallet_kit.batch_config'));

        // No platform configured → no platform services, no throttling stack.
        self::assertFalse($container->hasParameter('wallet_kit.apple.certificate_path'));
        self::assertFalse($container->hasParameter('wallet_kit.google.service_account_json_path'));
        self::assertFalse($container->hasParameter('wallet_kit.samsung.partner_id'));
    }

    public function testAppleConfigLoadsAppleServices(): void
    {
        $container = $this->createProcessedContainer([
            'apple' => [
                'certificatePath' => '/certs/pass.p12',
                'certificatePassword' => 'secret',
            ],
        ]);

        self::assertSame('/certs/pass.p12', $container->getParameter('wallet_kit.apple.certificate_path'));
        self::assertSame('secret', $container->getParameter('wallet_kit.apple.certificate_password'));
        self::assertNull($container->getParameter('wallet_kit.apple.wwdr_certificate_path'));
        self::assertNull($container->getParameter('wallet_kit.apple.apns_key_path'));
        self::assertNull($container->getParameter('wallet_kit.apple.apns_key_id'));
        self::assertNull($container->getParameter('wallet_kit.apple.apns_team_id'));
        self::assertNull($container->getParameter('wallet_kit.apple.team_identifier'));
        self::assertNull($container->getParameter('wallet_kit.apple.pass_type_identifier'));
        self::assertFalse($container->getParameter('wallet_kit.apple.apns_sandbox'));

        self::assertTrue($container->hasDefinition('wallet_kit.credentials.apple'));
        self::assertTrue($container->hasDefinition('wallet_kit.auth.apple_apns_jwt'));
        self::assertTrue($container->hasDefinition('wallet_kit.apple.packager'));
        self::assertTrue($container->hasDefinition('wallet_kit.apple.push_notifier'));
        self::assertTrue($container->hasDefinition('wallet_kit.controller.apple_web_service'));

        // The Apple processor joins the throttling tag set, even though the
        // Messenger stack itself is (optionally) not installed.
        self::assertTrue($container->getDefinition('wallet_kit.processor.apple_push')->hasTag('wallet_kit.pending_operation_processor'));
    }

    public function testGoogleConfigLoadsGoogleServices(): void
    {
        $container = $this->createProcessedContainer([
            'google' => [
                'serviceAccountJsonPath' => '/sa.json',
                'secretToken' => 'callback-secret',
            ],
        ]);

        self::assertSame('/sa.json', $container->getParameter('wallet_kit.google.service_account_json_path'));
        self::assertSame('callback-secret', $container->getParameter('wallet_kit.google.secret_token'));

        self::assertTrue($container->hasDefinition('wallet_kit.credentials.google'));
        self::assertTrue($container->hasDefinition('wallet_kit.auth.google_oauth2'));
        self::assertTrue($container->hasDefinition('wallet_kit.google.client'));
        self::assertTrue($container->hasDefinition('wallet_kit.google.save_link_generator'));
        self::assertTrue($container->hasDefinition('wallet_kit.controller.google_callback'));
        self::assertTrue($container->hasDefinition('wallet_kit.processor.google_api'));
    }

    public function testSamsungConfigLoadsSamsungServices(): void
    {
        $container = $this->createProcessedContainer([
            'samsung' => [
                'partnerId' => 'partner-1',
                'privateKeyPath' => '/key.pem',
                'certificateId' => 'YMtt',
                'publicKeyPath' => '/samsung.crt',
                'region' => 'kr',
            ],
        ]);

        self::assertSame('partner-1', $container->getParameter('wallet_kit.samsung.partner_id'));
        self::assertSame('/key.pem', $container->getParameter('wallet_kit.samsung.private_key_path'));
        self::assertSame('YMtt', $container->getParameter('wallet_kit.samsung.certificate_id'));
        self::assertSame('/samsung.crt', $container->getParameter('wallet_kit.samsung.public_key_path'));
        self::assertSame('kr', $container->getParameter('wallet_kit.samsung.region'));

        self::assertTrue($container->hasDefinition('wallet_kit.credentials.samsung'));
        self::assertTrue($container->hasDefinition('wallet_kit.auth.samsung_request'));
        self::assertTrue($container->hasDefinition('wallet_kit.samsung.client'));
        self::assertTrue($container->hasDefinition('wallet_kit.controller.samsung_callback'));
        self::assertTrue($container->hasDefinition('wallet_kit.samsung.notification_verifier'));
        self::assertTrue($container->hasDefinition('wallet_kit.processor.samsung_api'));
    }

    public function testBatchConfigAggregatesPlatformSettings(): void
    {
        $container = new ContainerBuilder();
        $extension = new WalletKitExtension();
        $extension->load([[
            'apple' => [
                'certificatePath' => '/c.p12',
                'certificatePassword' => '',
                'pushBatchSize' => 10,
                'pushBatchInterval' => 5,
            ],
            'google' => [
                'serviceAccountJsonPath' => '/sa.json',
                'apiBatchSize' => 20,
                'apiBatchInterval' => 7,
            ],
        ]], $container);

        $batchConfig = $container->getParameter('wallet_kit.batch_config');

        self::assertSame([
            'apple' => ['pushBatchSize' => 10, 'pushBatchInterval' => 5],
            'google' => ['apiBatchSize' => 20, 'apiBatchInterval' => 7],
        ], $batchConfig);
    }

    /**
     * Guards the DI regression where a partial install (e.g. Messenger present
     * but no Doctrine bundle) made the container compile fail on unresolved
     * references. The throttling stack must simply not be registered.
     */
    public function testThrottlingStackIsSkippedWithoutFullDependencies(): void
    {
        $container = new ContainerBuilder();
        $extension = new WalletKitExtension();
        $extension->load([['samsung' => [
            'partnerId' => 'p',
            'privateKeyPath' => '/k.pem',
            'certificateId' => 'YMtt',
        ]]], $container);

        $messengerAvailable = interface_exists(\Symfony\Component\Messenger\MessageBusInterface::class);
        $doctrineBundleAvailable = class_exists(\Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class);

        self::assertSame(
            $messengerAvailable && $doctrineBundleAvailable,
            $container->hasDefinition('wallet_kit.messenger.handler.process_pending_operations'),
            'Throttling stack registration must follow Messenger + DoctrineBundle availability.',
        );
    }

    public function testGoogleSecretTokenDefaultsToNull(): void
    {
        $container = $this->createProcessedContainer([
            'google' => ['serviceAccountJsonPath' => '/sa.json'],
        ]);

        self::assertNull($container->getParameter('wallet_kit.google.secret_token'));
    }
}
