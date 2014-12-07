<?php

namespace SHyx0rmZ\ElasticaEntityMapping\DependencyInjection\Compiler;

use Doctrine\Common\Annotations\AnnotationReader;
use SHyx0rmZ\ElasticaEntityMapping\Annotation\ElasticsearchMapping;
use SHyx0rmZ\ProjectScanner\ProjectScanner;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class ElasticaEntityMappingPass
 * @package SHyx0rmZ\ElasticaEntityMapping\DependencyInjection\Compiler
 * @author Patrick Pokatilo <mail@shyxormz.net>
 */
class ElasticaEntityMappingPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $scanner = new ProjectScanner();
        $reader = new AnnotationReader();
        $autoloadedClasses = array();

        for ($index = 0; $container->hasDefinition('shyxormz.elastica.mapping.factory.' . $index); ++$index) {
            $factory = $container->getDefinition('shyxormz.elastica.mapping.factory.' . $index);

            foreach ($scanner->findInDirectory('Entity') as $scanResult) {
                if (isset($autoloadedClasses[$scanResult->getReference()])) {
                    continue;
                }

                try {
                    $class = new \ReflectionClass($scanResult->getReference());
                } catch (\RuntimeException $e) {
                    if (!class_exists($scanResult->getReference(), false)) {
                        $autoloadedClasses[$scanResult->getReference()] = true;
                    }

                    continue;
                }

                $annotation = $reader->getClassAnnotation($class, ElasticsearchMapping::class);

                /** @var ElasticsearchMapping $annotation */
                if ($annotation) {
                    $this->ensurePropertiesExists($annotation, $class);

                    $file = $scanResult->getFileInfo()->getPath() . DIRECTORY_SEPARATOR . $annotation->file;

                    $this->ensureFileExists($file, $class);

                    $mapping = json_decode(file_get_contents($file), true);
                    $type = array_keys($mapping)[0];
                    $indices = empty($annotation->indices) ? array() : explode(',', $annotation->indices);

                    $this->ensureTypeValid($type, $file);

                    $factory->addMethodCall('addWatchdog', array($type, $file, $indices));
                }
            }

            $alias = $container->getAlias('shyxormz.elastica.mapping.factory.client.' . $index);
            $client = $container->getDefinition($alias);
            $client->setFactory(array(new Reference('shyxormz.elastica.mapping.factory.' . $index), 'createInstance'));
        }
    }

    /**
     * @param ElasticsearchMapping $annotation
     * @param \ReflectionCLass $class
     */
    private function ensurePropertiesExists(ElasticsearchMapping $annotation, \ReflectionCLass $class)
    {
        if (!isset($annotation->file)) {
            throw new \RuntimeException('ElasticsearchMapping needs property "file" in ' . $class->getName());
        }
    }

    /**
     * @param string $file
     * @param \ReflectionCLass $class
     */
    private function ensureFileExists($file, \ReflectionCLass $class)
    {
        if (!is_file($file)) {
            throw new \RuntimeException('ElasticsearchMapping file "' . $file . '" not found in ' . $class->getName());
        }
    }

    /**
     * @param mixed $type
     * @param string $file
     */
    private function ensureTypeValid($type, $file)
    {
        if (!is_string($type)) {
            throw new \RuntimeException('ElasticsearchMapping not valid: ' . $file);
        }
    }
}
