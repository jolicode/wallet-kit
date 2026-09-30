<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Bundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('wallet_kit');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('apple')
                    ->children()
                        ->scalarNode('certificatePath')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('certificatePassword')
                            // Empty passwords are allowed on purpose: unencrypted-scoped P12s exist.
                            ->isRequired()->info('May be empty when the P12 is not encryption-protected.')
                        ->end()
                        ->scalarNode('wwdrCertificatePath')->defaultNull()->end()
                        ->scalarNode('apnsKeyPath')->defaultNull()->end()
                        ->scalarNode('apnsKeyId')->defaultNull()->end()
                        ->scalarNode('apnsTeamId')->defaultNull()->end()
                        ->scalarNode('teamIdentifier')->defaultNull()->end()
                        ->scalarNode('passTypeIdentifier')->defaultNull()->end()
                        ->booleanNode('apnsSandbox')->defaultFalse()->end()
                        ->integerNode('pushBatchSize')->min(1)->defaultValue(500)->end()
                        ->integerNode('pushBatchInterval')->min(0)->defaultValue(300)->end()
                    ->end()
                ->end()
                ->arrayNode('google')
                    ->children()
                        ->scalarNode('serviceAccountJsonPath')->isRequired()->cannotBeEmpty()->end()
                        // Google does not sign callbacks. When set, the callback controller
                        // requires this shared secret in the Authorization header — pair it
                        // with a proxy/edge rule, see docs/bundle.md.
                        ->scalarNode('secretToken')->defaultNull()->end()
                        ->integerNode('apiBatchSize')->min(1)->defaultValue(50)->end()
                        ->integerNode('apiBatchInterval')->min(0)->defaultValue(60)->end()
                    ->end()
                ->end()
                ->arrayNode('samsung')
                    ->children()
                        ->scalarNode('partnerId')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('privateKeyPath')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('certificateId')
                            ->isRequired()
                            ->cannotBeEmpty()
                            ->info('Your certificate identifier from the Samsung Partner site, used in every Authorization JWS header.')
                        ->end()
                        ->scalarNode('publicKeyPath')
                            ->defaultNull()
                            ->info('Samsung public certificate (PEM) — verifies inbound notification JWS signatures. Leave null to skip verification (logged warning).')
                        ->end()
                        ->enumNode('region')->values(['us', 'eu', 'kr'])->defaultValue('eu')->end()
                        ->integerNode('apiBatchSize')->min(1)->defaultValue(100)->end()
                        ->integerNode('apiBatchInterval')->min(0)->defaultValue(30)->end()
                    ->end()
                ->end()
                ->scalarNode('route_prefix')->defaultValue('/wallet-kit')->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
