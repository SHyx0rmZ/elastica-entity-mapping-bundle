<?php

namespace SHyx0rmZ\ElasticaEntityMapping\DependencyInjection\Compiler;

use Doctrine\Common\Annotations\AnnotationReader;
use SHyx0rmZ\ElasticaEntityMapping\Annotation\ElasticsearchMapping;
use SHyx0rmZ\ElasticaEntityMapping\Component\VendorScanner;
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
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $reader = new AnnotationReader();
        $factory = $container->getDefinition('shyxormz.elastica.mapping.factory');

        foreach ($this->yieldEntities() as $path => $entity) {
            $class = new \ReflectionClass($entity);
            $annotation = $reader->getClassAnnotation($class, ElasticsearchMapping::class);

            /** @var ElasticsearchMapping $annotation */
            if ($annotation) {
                $this->ensurePropertiesExists($annotation, $class);

                $file = dirname($path) . DIRECTORY_SEPARATOR . $annotation->file;

                $this->ensureFileExists($file, $class);

                $mapping = json_decode(file_get_contents($file), true);
                $type = array_keys($mapping)[0];
                $indices = empty($annotation->indices) ? array() : explode(',', $annotation->indices);

                $this->ensureTypeValid($type, $file);

                $factory->addMethodCall('addWatchdog', array($type, $file, $indices));
            }
        }

        $alias = $container->getAlias('shyxormz.elastica.mapping.factory.client');
        $client = $container->getDefinition($alias);
        $client->setFactoryService('shyxormz.elastica.mapping.factory');
        $client->setFactoryMethod('createInstance');
    }

    /**
     * @return string[]
     */
    private function yieldEntities()
    {
        $scanner = new VendorScanner();

        foreach ($scanner->yieldIncludeDirectories() as $namespace => $includeDir) {
            $finder = new Finder();

            try {
                $finder->files()->name('*.php')->in($includeDir . '/Entity');
            } catch (\InvalidArgumentException $e) {
                continue;
            }

            /** @var SplFileInfo $file */
            foreach ($finder as $file) {
                yield $file->getRealPath() => $this->buildClassName($namespace, $file);
            }
        }
    }

    /**
     * @param $namespace
     * @param SplFileInfo $file
     * @return string
     */
    private function buildClassName($namespace, SplFileInfo $file)
    {
        return str_replace('\\\\', '\\', '\\' . $namespace . '\\Entity\\' . str_replace('/', '\\', $file->getRelativePath()) . '\\' . $file->getBasename('.php'));
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
