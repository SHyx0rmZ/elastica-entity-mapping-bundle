<?php

namespace SHyx0rmZ\ElasticaEntityMapping;

use SHyx0rmZ\ElasticaEntityMapping\DependencyInjection\Compiler\ElasticsearchWatchdogPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class ElasticaEntityMappingBundle
 * @package SHyx0rmZ\ElasticaEntityMapping
 * @author Patrick Pokatilo <mail@shyxormz.net>
 */
class ElasticaEntityMappingBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ElasticsearchWatchdogPass());
    }
}
