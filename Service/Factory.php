<?php

namespace SHyx0rmZ\ElasticaEntityMapping\Service;

use Psr\Log\LoggerInterface;
use SHyx0rmZ\ElasticaEntityMapping\Component\Elasticsearch\ElasticsearchConnectorFactory;
use SHyx0rmZ\ElasticaEntityMapping\Component\Elasticsearch\ElasticsearchConnectorInterface;
use SHyx0rmZ\ElasticaEntityMapping\Component\MappingConflictDetector;
use SHyx0rmZ\ElasticaEntityMapping\Component\MappingUpdater;
use SHyx0rmZ\ElasticaEntityMapping\Component\Watchdog;
use SHyx0rmZ\ElasticaEntityMapping\DependencyInjection\ClientConfiguration;
use SHyx0rmZ\ElasticaEntityMapping\DependencyInjection\IndexConfiguration;

/**
 * Class Factory
 * @package SHyx0rmZ\ElasticaEntityMapping\Service
 * @author Patrick Pokatilo <mail@shyxormz.net>
 */
class Factory
{
    /** @var ClientConfiguration */
    private $clientConfig;

    /** @var Watchdog[] */
    private $watchdogs = array();

    /** @var LoggerInterface */
    private $logger;

    /** @var string */
    private $clientClass;

    /**
     * @param array $config
     * @param LoggerInterface $logger
     * @param string $clientClass
     */
    public function __construct(array $config, LoggerInterface $logger, $clientClass)
    {
        $this->clientConfig = new ClientConfiguration($config);
        $this->logger = $logger;
        $this->clientClass = $clientClass;
    }

    /**
     * @param string $fileName
     * @param array $indices
     * @param string $typeName
     */
    public function addWatchdog($fileName, array $indices, $typeName)
    {
        $mapping = json_decode(file_get_contents($fileName), true);
        $mapping = $mapping[$typeName]['properties'];

        $this->watchdogs[] = new Watchdog($typeName, $mapping, $indices, $fileName);
    }

    /**
     * @return object
     */
    public function createInstance()
    {
        $class = new \ReflectionClass($this->clientClass);
        $client = $class->newInstanceArgs(func_get_args());
        $connector = ElasticsearchConnectorFactory::createConnector($this->clientClass, $client);

        $this->detectConflicts($connector);

        foreach ($this->clientConfig->getIndices() as $indexConfig) {
            $this->applyWatchdogs($connector, $indexConfig);
        }

        return $client;
    }

    /**
     * @param ElasticsearchConnectorInterface $connector
     * @param IndexConfiguration $indexConfig
     */
    private function applyWatchdogs(ElasticsearchConnectorInterface $connector, IndexConfiguration $indexConfig)
    {
        foreach ($this->watchdogs as $watchdog) {
            if ($this->noIndexRestraint($watchdog) || $this->fulfillsIndexRestraint($watchdog, $indexConfig)) {
                $type = ElasticsearchConnectorFactory::createTypeWrapperFromConnector($connector, $indexConfig->getName(), $watchdog->getTypeName());
                $updater = new MappingUpdater($this->logger, $type);

                if ($updater->needsUpdate($watchdog->getMapping())) {
                    if ($this->clientConfig->shouldUpdate()) {
                        $updater->updateMapping($watchdog->getMapping(), $indexConfig->getSettings());
                    } else {
                        throw new \RuntimeException('Elasticsearch mapping changed: ' . $updater->getTypeAddress());
                    }
                }
            }
        }
    }

    /**
     * @param ElasticsearchConnectorInterface $connector
     */
    private function detectConflicts(ElasticsearchConnectorInterface $connector)
    {
        $differentTypesDetector = new MappingConflictDetector();

        foreach ($this->clientConfig->getIndices() as $indexConfig) {
            $sameTypeDetector = new MappingConflictDetector();

            foreach ($this->watchdogs as $watchdog) {
                $type = ElasticsearchConnectorFactory::createTypeWrapperFromConnector($connector, $indexConfig->getName(), $watchdog->getTypeName());

                if ($this->noIndexRestraint($watchdog) || $this->fulfillsIndexRestraint($watchdog, $indexConfig)) {
                    $differentTypesDetector->remember($watchdog, $type);
                    $sameTypeDetector->remember($watchdog, $type);
                }
            }

            if (($conflict = $sameTypeDetector->detectSameTypeConflict()) !== null) {
                throw new \RuntimeException($conflict->getMessage());
            }
        }

        if (($conflict = $differentTypesDetector->detectDifferentTypeConflict()) !== null) {
            throw new \RuntimeException($conflict->getMessage());
        }
    }

    /**
     * @param Watchdog $watchdog
     * @return bool
     */
    private function noIndexRestraint(Watchdog $watchdog)
    {
        return empty($watchdog->getIndices());
    }

    /**
     * @param Watchdog $watchdog
     * @param IndexConfiguration $indexConfig
     * @return bool
     */
    private function fulfillsIndexRestraint(Watchdog $watchdog, IndexConfiguration $indexConfig)
    {
        return ($indexConfig->hasAlias() && in_array($indexConfig->getAlias(), $watchdog->getIndices()));
    }
}
