<?php

namespace SHyx0rmZ\ElasticaEntityMapping\DependencyInjection;

use SHyx0rmZ\ElasticaEntityMapping\Service\Factory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class ElasticaEntityMappingExtension
 * @package SHyx0rmZ\ElasticaEntityMapping\DependencyInjection
 * @author Patrick Pokatilo <mail@shyxormz.net>
 */
class ElasticaEntityMappingExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new ElasticaEntityMappingConfiguration();

        $config = $this->processConfiguration($configuration, $configs);

        $aliases = array();

        foreach ($config['clients'] as $clientConfig) {
            foreach ($clientConfig['indices'] as $indexConfig) {
                if (isset($indexConfig['alias'], $aliases[$indexConfig['alias']])) {
                    throw new \RuntimeException('Duplicate index alias encountered while building Elastica watchdogs: ' . $indexConfig['alias']);
                }

                $aliases[$indexConfig['alias']] = true;
            }
        }

        for ($index = 0; $index < count($config['clients']); ++$index) {
            $factory = $container->register('shyxormz.elastica.mapping.factory.' . $index, Factory::class);
            $factory->setPublic(false);
            $factory->addArgument($config['clients'][$index]);
            $factory->addArgument(new Reference('logger'));

            $container->setAlias('shyxormz.elastica.mapping.factory.client.' . $index, $config['clients'][$index]['service']);
            $alias = $container->getAlias('shyxormz.elastica.mapping.factory.client.' . $index);
            $alias->setPublic(false);
        }
    }
}
