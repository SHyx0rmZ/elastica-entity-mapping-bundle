<?php

namespace SHyx0rmZ\ElasticaEntityMapping\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class ElasticaEntityMappingConfiguration
 * @package SHyx0rmZ\ElasticaEntityMapping\DependencyInjection
 * @author Patrick Pokatilo <mail@shyxormz.net>
 */
class ExtensionConfiguration implements ConfigurationInterface
{
    /**
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();
        $root = $builder->root('elastica_entity_mapping');

        $root
            ->children()
                ->arrayNode('clients')
                    ->isRequired()
                    ->prototype('array')
                        ->children()
                            ->scalarNode('service')
                                ->isRequired()
                            ->end()
                            ->booleanNode('update')
                                ->isRequired()
                            ->end()
                            ->arrayNode('indices')
                                ->isRequired()
                                ->requiresAtLeastOneElement()
                                ->prototype('array')
                                    ->children()
                                        ->scalarNode('name')
                                            ->isRequired()
                                        ->end()
                                        ->scalarNode('alias')->end()
                                        ->scalarNode('settings')->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $builder;
    }
}
