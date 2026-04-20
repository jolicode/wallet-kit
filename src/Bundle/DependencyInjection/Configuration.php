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
                        ->scalarNode('certificatePassword')->isRequired()->end()
                        ->scalarNode('wwdrCertificatePath')->defaultNull()->end()
                        ->scalarNode('apnsKeyPath')->defaultNull()->end()
                        ->scalarNode('apnsKeyId')->defaultNull()->end()
                        ->scalarNode('apnsTeamId')->defaultNull()->end()
                        ->scalarNode('teamIdentifier')->defaultNull()->end()
                        ->scalarNode('passTypeIdentifier')->defaultNull()->end()
                        ->booleanNode('apnsSandbox')->defaultFalse()->end()
                        ->integerNode('pushBatchSize')->defaultValue(500)->end()
                        ->integerNode('pushBatchInterval')->defaultValue(300)->end()
                    ->end()
                ->end()
                ->arrayNode('google')
                    ->children()
                        ->scalarNode('serviceAccountJsonPath')->isRequired()->cannotBeEmpty()->end()
                        ->integerNode('apiBatchSize')->defaultValue(50)->end()
                        ->integerNode('apiBatchInterval')->defaultValue(60)->end()
                    ->end()
                ->end()
                ->arrayNode('samsung')
                    ->children()
                        ->scalarNode('partnerId')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('privateKeyPath')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('serviceId')->defaultNull()->end()
                        ->enumNode('region')->values(['us', 'eu', 'kr'])->defaultValue('eu')->end()
                        ->integerNode('apiBatchSize')->defaultValue(100)->end()
                        ->integerNode('apiBatchInterval')->defaultValue(30)->end()
                    ->end()
                ->end()
                ->scalarNode('route_prefix')->defaultValue('/wallet-kit')->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
