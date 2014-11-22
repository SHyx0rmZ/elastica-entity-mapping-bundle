<?php

namespace SHyx0rmZ\ElasticaEntityMapping\Service;

use Elastica\Client;
use Elastica\Exception\ResponseException;
use Elastica\Index;
use Elastica\Type;
use Psr\Log\LoggerInterface;

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
    private $watches;
    /** @var LoggerInterface */
    private $logger;

    public function __construct(array $config, LoggerInterface $logger)
    {
        $this->indices = $config['indices'];
        $this->shouldUpdate = $config['update'];
        $this->logger = $logger;
    }

    public function addWatchdog($type, $file, $indices)
    {
        $mapping = json_decode(file_get_contents($file), true);
        $mapping = $mapping[$type]['properties'];
        $this->watches[$type] = (object)array(
            'mapping' => $mapping,
            'indices' => $indices
        );
    }

    public function createInstance()
    {
        /** @var Client $client */
        $class = new \ReflectionClass(Client::class);
        $client = $class->newInstanceArgs(func_get_args());
        $baseDir = __DIR__ . '/../../../../';

        foreach ($this->indices as $indexConfig) {
            $indexName = $indexConfig['name'];
            $indexAlias = isset($indexConfig['alias']) ? $indexConfig['alias'] : null;
            $indexSettings = isset($indexConfig['settings']) ? $indexConfig['settings'] : null;

            if ($indexSettings !== null) {
                $file = $baseDir . $indexSettings;

                if (!is_file($file)) {
                    throw new \RuntimeException('ElasticsearchMapping file "' . $file . '" not found');
                }

                $indexSettings = json_decode(file_get_contents($file), true);
            }

            $index = new Index($client, $indexName);

            foreach ($this->watches as $typeName => $typeInfo) {
                if (empty($typeInfo->indices) || ($indexAlias !== null && in_array($indexAlias, $typeInfo->indices))) {
                    $type = new Type($index, $typeName);

                    $currentMapping = $type->getMapping();
                    $currentMapping = @$currentMapping[$indexName]['mappings'][$typeName]['properties'];

                    if ($currentMapping != $typeInfo->mapping) {
                        if ($this->shouldUpdate) {
                            try {
                                $this->logger->info('Updating elasticsearch mapping: ' . $this->getTypeAddress($type));
                                $type->setMapping($typeInfo->mapping);
                            } catch (ResponseException $e) {
                                $this->logger->error('Error while updating elasticsearch mapping, trying to update settings');

                                if ($indexSettings !== null) {
                                    $this->logger->info('Updating elasticsearch settings: ' . $this->getIndexAddress($index));
                                    $index->close();
                                    $index->setSettings($indexSettings);
                                    $index->open();
                                }

                                $this->logger->info('Updating elasticsearch mapping: ' . $this->getTypeAddress($type));
                                $type->setMapping($typeInfo->mapping);
                            }
                        } else {
                            throw new \RuntimeException('Elasticsearch mapping changed: ' . $this->getTypeAddress($type));
                        }
                    }
                }
            }
        }

        return $client;
    }

    private function getIndexAddress(Index $index)
    {
        $indexName = $index->getName();
        $client = $index->getClient();
        $connection = $client->getConnection();

        return strtolower($connection->getTransport()) . '://' . $connection->getHost() . ':' . $connection->getPort() . '/' . $indexName;
    }

    private function getTypeAddress(Type $type)
    {
        $typeName = $type->getName();
        $index = $type->getIndex();

        return $this->getIndexAddress($index) . '/' . $typeName;
    }
}
