<?php

namespace SHyx0rmZ\ElasticaEntityMapping\DependencyInjection\Compiler;

use Doctrine\Common\Annotations\AnnotationReader;
use SHyx0rmZ\ElasticaEntityMapping\Annotation\ElasticsearchMapping;
use SHyx0rmZ\ElasticaEntityMapping\DependencyInjection\ServiceNamingScheme;
use SHyx0rmZ\ProjectScanner\ProjectScanner;
use SHyx0rmZ\ProjectScanner\ScanResult\ScanResultInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class ElasticsearchWatchdogPass
 * @package SHyx0rmZ\ElasticaEntityMapping\DependencyInjection\Compiler
 * @author Patrick Pokatilo <mail@shyxormz.net>
 */
class ElasticsearchWatchdogPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $scanner = new ProjectScanner();
        $reader = new AnnotationReader();

        foreach ($scanner->findInDirectory('Entity') as $scanResult) {
            $class = $this->getReflectionClass($scanResult->getReference());

            if ($class === null) {
                continue;
            }

            $annotations = $reader->getClassAnnotations($class);

            for ($index = 0; $container->hasDefinition(ServiceNamingScheme::getFactoryName($index)); ++$index) {
                $factory = $container->getDefinition(ServiceNamingScheme::getFactoryName($index));

                $this->processAnnotations($class, $annotations, $scanResult, $factory);
            }
        }

        for ($index = 0; $container->hasDefinition(ServiceNamingScheme::getFactoryName($index)); ++$index) {
            $factory = $container->getDefinition(ServiceNamingScheme::getFactoryName($index));

            $alias = $container->getAlias(ServiceNamingScheme::getClientAlias($index));
            $client = $container->getDefinition($alias);
            $client->setFactory(array(new Reference(ServiceNamingScheme::getFactoryName($index)), 'createInstance'));

            $factory->addArgument($client->getClass());
        }
    }

    /**
     * @param \ReflectionClass $class
     * @param ElasticsearchMapping[] $annotations
     * @param ScanResultInterface $scanResult
     * @param Definition $factory
     */
    private function processAnnotations(
        \ReflectionClass $class,
        array $annotations,
        ScanResultInterface $scanResult,
        Definition $factory
    ) {
        foreach ($annotations as $annotation) {
            if ($annotation instanceof ElasticsearchMapping) {
                $this->ensurePropertiesExists($annotation, $class);

                $file = $scanResult->getFileInfo()->getPath() . DIRECTORY_SEPARATOR . $annotation->file;

                $this->ensureFileExists($file, $class);

                $mapping = json_decode(file_get_contents($file), true);
                $type = array_keys($mapping)[0];
                $indices = empty($annotation->indices) ? array() : explode(',', $annotation->indices);

                $this->ensureTypeValid($type, $file);

                $factory->addMethodCall('addWatchdog', array($file, $indices, $type));
            }
        }
    }

    /**
     * @param string $class
     * @return \ReflectionClass|null
     */
    public function getReflectionClass($class)
    {
        static $autoloadedClasses = array();

        if (isset($autoloadedClasses[$class])) {
            return null;
        }

        try {
            return new \ReflectionClass($class);
        } catch (\RuntimeException $e) {
            if (!class_exists($class, false)) {
                $autoloadedClasses[$class] = true;
            }

            return null;
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
