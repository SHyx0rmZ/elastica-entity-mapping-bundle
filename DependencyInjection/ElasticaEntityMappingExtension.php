<?php

namespace SHyx0rmZ\ElasticaEntityMapping\DependencyInjection;

use SHyx0rmZ\ElasticaEntityMapping\Service\Factory;
use SHyx0rmZ\ServicesLoader\Extension\ServicesLoaderExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class ElasticaEntityMappingExtension
 * @package SHyx0rmZ\ElasticaEntityMapping\DependencyInjection
 * @author Patrick Pokatilo <mail@shyxormz.net>
 */
class ElasticaEntityMappingExtension extends ServicesLoaderExtension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        parent::load($configs, $container);

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $aliases = array();
        $parser = new ConfigurationParser($config);

        foreach ($parser->getClients() as $clientConfig) {
            foreach ($clientConfig->getIndices() as $indexConfig) {
                if ($indexConfig->hasAlias()) {
                    if (isset($aliases[$indexConfig->getAlias()])) {
                        throw new \RuntimeException('Duplicate index alias encountered while building Elastica watchdogs: ' . $indexConfig['alias']);
                    }

                    $aliases[$indexConfig->getAlias()] = true;
                }
            }
        }

        unset($aliases);

        foreach ($parser->getClients() as $index => $clientConfig) {
            $factory = $container->register('shyxormz.elastica.mapping.factory.' . $index, Factory::class);
            $factory->addArgument($config['clients'][$index]);
            $factory->addArgument(new Reference('logger'));

            $container->setAlias('shyxormz.elastica.mapping.factory.client.' . $index, $clientConfig->getServiceName());
            $alias = $container->getAlias('shyxormz.elastica.mapping.factory.client.' . $index);
            $alias->setPublic(false);
        }
    }

    public function getConfiguration(array $configs, ContainerBuilder $container)
    {
        return new ExtensionConfiguration();
    }
}
