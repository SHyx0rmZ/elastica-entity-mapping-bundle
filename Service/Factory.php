<?php

namespace SHyx0rmZ\ElasticaEntityMapping\Service;

use Elastica\Client;
use Elastica\Index;
use Elastica\Type;
use Psr\Log\LoggerInterface;
use SHyx0rmZ\ElasticaEntityMapping\Component\IndexSettingsContainer;
use SHyx0rmZ\ElasticaEntityMapping\Component\MappingConflictDetector;
use SHyx0rmZ\ElasticaEntityMapping\Component\MappingUpdater;
use SHyx0rmZ\ElasticaEntityMapping\Component\Watchdog;

/**
 * Class Factory
 * @package SHyx0rmZ\ElasticaEntityMapping\Service
 * @author Patrick Pokatilo <mail@shyxormz.net>
 */
class Factory
{
    /** @var array */
    private $indices;

    /** @var boolean */
    private $shouldUpdate;

    /** @var Watchdog[] */
    private $watchdogs = array();

    /** @var LoggerInterface */
    private $logger;

    /**
     * @param array $config
     * @param LoggerInterface $logger
     */
    public function __construct(array $config, LoggerInterface $logger)
    {
        $this->indices = $config['indices'];
        $this->shouldUpdate = $config['update'];
        $this->logger = $logger;
    }

    /**
     * @param string $type
     * @param string $file
     * @param array $indices
     */
    public function addWatchdog($type, $file, array $indices)
    {
        $mapping = json_decode(file_get_contents($file), true);
        $mapping = $mapping[$type]['properties'];
        $this->watchdogs[] = new Watchdog($type, $mapping, $indices, $file);
    }

    /**
     * @return Client
     */
    public function createInstance()
    {
        /** @var Client $client */
        $class = new \ReflectionClass(Client::class);
        $client = $class->newInstanceArgs(func_get_args());

        $this->detectConflicts($client);

        foreach ($this->indices as $indexConfig) {
            $settingsContainer = new IndexSettingsContainer($indexConfig);

            $this->applyWatchdogs($client, $settingsContainer);
        }

        return $client;
    }

    /**
     * @param Client $client
     * @param IndexSettingsContainer $settingsContainer
     */
    private function applyWatchdogs(Client $client, IndexSettingsContainer $settingsContainer)
    {
        foreach ($this->watchdogs as $watchdog) {
            if ($this->noIndexRestraint($watchdog) || $this->fulfillsIndexRestraint($watchdog, $settingsContainer)) {
                $type = new Type(new Index($client, $settingsContainer->getName()), $watchdog->getTypeName());
                $updater = new MappingUpdater($this->logger, $type);

                if ($updater->needsUpdate($watchdog->getMapping())) {
                    if ($this->shouldUpdate) {
                        $updater->updateMapping($watchdog->getMapping(), $settingsContainer->getSettings());
                    } else {
                        throw new \RuntimeException('Elasticsearch mapping changed: ' . $updater->getTypeAddress());
                    }
                }
            }
        }
    }

    /**
     * @param Client $client
     */
    private function detectConflicts(Client $client)
    {
        $differentTypesDetector = new MappingConflictDetector();

        foreach ($this->indices as $indexConfig) {
            $settingsContainer = new IndexSettingsContainer($indexConfig);
            $sameTypeDetector = new MappingConflictDetector();

            foreach ($this->watchdogs as $watchdog) {
                $type = new Type(new Index($client, $settingsContainer->getName()), $watchdog->getTypeName());

                if ($this->noIndexRestraint($watchdog) || $this->fulfillsIndexRestraint($watchdog, $settingsContainer)) {
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
     * @param IndexSettingsContainer $settings
     * @return bool
     */
    private function fulfillsIndexRestraint(Watchdog $watchdog, IndexSettingsContainer $settings)
    {
        return ($settings->getAlias() !== null && in_array($settings->getAlias(), $watchdog->getIndices()));
    }
}
