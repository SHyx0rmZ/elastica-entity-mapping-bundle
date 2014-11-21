<?php

namespace SHyx0rmZ\ElasticaEntityMapping\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class ElasticaEntityMappingConfiguration
 * @package SHyx0rmZ\ElasticaEntityMapping\DependencyInjection
 * @author Patrick Pokatilo <mail@shyxormz.net>
 */
class ElasticaEntityMappingConfiguration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();
        $root = $builder->root('elastica_entity_mapping');

        $root
            ->isRequired()
            ->children()
                ->booleanNode('update')
                    ->isRequired()
                ->end()
                ->arrayNode('indices')
                    ->isRequired()
                    ->requiresAtLeastOneElement()
                    ->prototype('scalar')->end()
                ->end()
            ->end();

        return $builder;
    }
}
