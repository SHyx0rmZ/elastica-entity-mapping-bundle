<?php

namespace SHyx0rmZ\ElasticaEntityMapping\DependencyInjection\Compiler;

use Doctrine\Common\Annotations\AnnotationReader;
use SHyx0rmZ\ElasticaEntityMapping\Annotation\ElasticsearchMapping;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Class ElasticaEntityMappingPass
 * @package SHyx0rmZ\ElasticaEntityMapping\DependencyInjection\Compiler
 * @author Patrick Pokatilo <mail@shyxormz.net>
 */
class ElasticaEntityMappingPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $reader = new AnnotationReader();
        $factory = $container->getDefinition('shyxormz.elastica.mapping.factory');

        foreach ($this->yieldEntities() as $path => $entity) {
            $class = new \ReflectionClass($entity);
            $annotation = $reader->getClassAnnotation($class, ElasticsearchMapping::class);

            /** @var ElasticsearchMapping $annotation */
            if ($annotation) {
                if (!isset($annotation->file)) {
                    throw new \RuntimeException('ElasticsearchMapping needs property "file" in ' . $class->getName());
                }

                $file = dirname($path) . DIRECTORY_SEPARATOR . $annotation->file;

                if (!is_file($file)) {
                    throw new \RuntimeException('ElasticsearchMapping file "' . $file . '" not found in ' . $class->getName());
                }

                $mapping = json_decode(file_get_contents($file), true);
                $type = array_keys($mapping)[0];
                $indices = empty($annotation->indices) ? array() : explode(',', $annotation->indices);

                if (!is_string($type)) {
                    throw new \RuntimeException('ElasticsearchMapping not valid: ' . $file);
                }

                $factory->addMethodCall('addWatchdog', array($type, $file, $indices));
            }
        }

        $client = $container->getDefinition('elastic.client');
        $client->setFactoryService('shyxormz.elastica.mapping.factory');
        $client->setFactoryMethod('createInstance');
    }

    /**
     * @return \Generator
     */
    private function yieldEntities()
    {
        $vendorDir = __DIR__ . '/../../../..';
        $autoloadDirs = array('/include_paths.php', '/autoload_namespaces.php', '/autoload_psr4.php', '/autoload_classmap.php', '/autoload_files.php');

        foreach ($autoloadDirs as $autoloadDir) {
            $autoloadMaps = require($vendorDir . '/composer' . $autoloadDir);

            foreach ($autoloadMaps as $namespace => $autoloadMap) {
                if (!is_string($namespace)) {
                    $namespace = '';
                }

                if (!is_array($autoloadMap)) {
                    $autoloadMap = array($autoloadMap);
                }

                foreach ($autoloadMap as $includeDir) {
                    $finder = new Finder();

                    try {
                        $finder->files()->name('*.php')->in($includeDir . '/Entity');
                    } catch (\InvalidArgumentException $e) {
                        continue;
                    }

                    /** @var SplFileInfo $file */
                    foreach ($finder as $file) {
                        yield $file->getRealPath() => str_replace('\\\\', '\\', '\\' . $namespace . '\\Entity\\' . str_replace('/', '\\', $file->getRelativePath()) . '\\' . $file->getBasename('.php'));
                    }
                }
            }
        }
    }
}
