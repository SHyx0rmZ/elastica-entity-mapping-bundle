<?php

namespace SHyx0rmZ\ElasticaEntityMapping\Component;

use Elastica\Client;
use Elastica\Exception\ResponseException;
use Elastica\Index;
use Elastica\Type;
use Psr\Log\LoggerInterface;

/**
 * Class MappingUpdater
 * @package SHyx0rmZ\ElasticaEntityMapping\Component
 * @author Patrick Pokatilo
 */
class MappingUpdater
{
    /** @var LoggerInterface */
    private $logger;
    /** @var Client */
    private $client;
    /** @var Index */
    private $index;
    /** @var Type */
    private $type;

    /**
     * @param LoggerInterface $logger
     * @param Client $client
     * @param IndexSettingsContainer $config
     * @param $type
     */
    public function __construct(LoggerInterface $logger, Client $client, IndexSettingsContainer $config, $type)
    {
        $this->logger = $logger;
        $this->client = $client;
        $this->index = new Index($this->client, $config->getName());
        $this->type = new Type($this->index, $type);
    }

    /**
     * @param array $mapping
     * @return bool
     */
    public function needsUpdate(array $mapping)
    {
        $currentMapping = $this->type->getMapping();
        $currentMapping = @$currentMapping[$this->index->getName()]['mappings'][$this->type->getName()]['properties'];

        return $currentMapping != $mapping;
    }

    /**
     * @return string
     */
    public function getIndexAddress()
    {
        $indexName = $this->index->getName();
        $connection = $this->client->getConnection();

        return strtolower($connection->getTransport()) . '://' . $connection->getHost() . ':' . $connection->getPort() . '/' . $indexName;
    }

    /**
     * @return string
     */
    public function getTypeAddress()
    {
        $typeName = $this->type->getName();

        return $this->getIndexAddress() . '/' . $typeName;
    }

    /**
     * @param array $mapping
     * @param array $settings
     */
    public function updateMapping(array $mapping, array $settings = array())
    {
        try {
            $this->updateType($mapping);
        } catch (ResponseException $e) {
            $this->logger->error('Error while updating elasticsearch mapping, trying to update settings');

            if ($settings != array()) {
                $this->updateIndex($settings);
            }

            $this->updateType($mapping);
        }
    }

    /**
     * @param array $settings
     */
    private function updateIndex(array $settings)
    {
        $this->logger->info('Updating elasticsearch settings: ' . $this->getIndexAddress());
        $this->index->close();
        $this->index->setSettings($settings);
        $this->index->open();
    }

    /**
     * @param array $mapping
     */
    private function updateType(array $mapping)
    {
        $this->logger->info('Updating elasticsearch mapping: ' . $this->getTypeAddress());
        $this->type->setMapping($mapping);
    }
}
