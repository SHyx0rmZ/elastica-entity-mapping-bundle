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
        $factory = $container->register('shyxormz.elastica.mapping.factory', Factory::class);
        $factory->setPublic(false);
        $factory->addArgument($config);
        $factory->addArgument(new Reference('logger'));

        $container->setAlias('shyxormz.elastica.mapping.factory.client', $config['client']);
        $alias = $container->getAlias('shyxormz.elastica.mapping.factory.client');
        $alias->setPublic(false);
    }
}
