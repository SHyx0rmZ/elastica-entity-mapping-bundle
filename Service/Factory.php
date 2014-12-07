<?php

namespace SHyx0rmZ\ElasticaEntityMapping\Service;

use Elastica\Client;
use Psr\Log\LoggerInterface;
use SHyx0rmZ\ElasticaEntityMapping\Component\IndexSettingsContainer;
use SHyx0rmZ\ElasticaEntityMapping\Component\MappingUpdater;

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

    /** @var array */
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
        $this->watchdogs[] = (object)array(
            'mapping' => $mapping,
            'indices' => $indices,
            'name' => $type
        );
    }

    /**
     * @return Client
     */
    public function createInstance()
    {
        /** @var Client $client */
        $class = new \ReflectionClass(Client::class);
        $client = $class->newInstanceArgs(func_get_args());

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
        foreach ($this->watchdogs as $typeInfo) {
            if ($this->noIndexRestraint($typeInfo) || $this->fullfillsIndexRestraint($typeInfo, $settingsContainer)) {
                $updater = new MappingUpdater($this->logger, $client, $settingsContainer, $typeInfo->name);

                if ($updater->needsUpdate($typeInfo->mapping)) {
                    if ($this->shouldUpdate) {
                        $updater->updateMapping($typeInfo->mapping, $settingsContainer->getSettings());
                    } else {
                        throw new \RuntimeException('Elasticsearch mapping changed: ' . $updater->getTypeAddress());
                    }
                }
            }
        }
    }

    /**
     * @param $typeInfo
     * @return bool
     */
    private function noIndexRestraint($typeInfo)
    {
        return empty($typeInfo->indices);
    }

    /**
     * @param $typeInfo
     * @param IndexSettingsContainer $settings
     * @return bool
     */
    private function fullfillsIndexRestraint($typeInfo, IndexSettingsContainer $settings)
    {
        return ($settings->getAlias() !== null && in_array($settings->getAlias(), $typeInfo->indices));
    }
}
