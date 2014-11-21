<?php

namespace SHyx0rmZ\ElasticaEntityMapping;

use SHyx0rmZ\ElasticaEntityMapping\DependencyInjection\Compiler\ElasticaEntityMappingPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ElasticaEntityMappingBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ElasticaEntityMappingPass());
    }
}
