<?php

namespace SHyx0rmZ\ElasticaEntityMapping\Service;

use Elastica\Client;
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

    public function addWatchdog($type, $file)
    {
        $mapping = json_decode(file_get_contents($file), true);
        $mapping = $mapping[$type]['properties'];

        $this->watches[$type] = $mapping;
    }

    public function createInstance()
    {
        /** @var Client $client */
        $class = new \ReflectionClass(Client::class);
        $client = $class->newInstanceArgs(func_get_args());

        foreach ($this->indices as $indexName) {
            foreach ($this->watches as $typeName => $mapping) {
                $type = new Type(new Index($client, $indexName), $typeName);

                $currentMapping = $type->getMapping();
                $currentMapping = @$currentMapping[$indexName]['mappings'][$typeName]['properties'];

                if ($currentMapping != $mapping) {
                    if ($this->shouldUpdate) {
                        $this->logger->info('Updating elasticsearch mapping: ' . $this->getTypeAddress($type));
                        $type->setMapping($mapping);
                    } else {
                        throw new \RuntimeException('Elasticsearch mapping changed: ' . $this->getTypeAddress($type));
                    }
                }
            }
        }

        return $client;
    }

    private function getTypeAddress(Type $type)
    {
        $typeName = $type->getName();
        $index = $type->getIndex();
        $indexName = $index->getName();
        $client = $index->getClient();
        $connection = $client->getConnection();

        return strtolower($connection->getTransport()) . '://' . $connection->getHost() . ':' . $connection->getPort() . '/' . $indexName . '/' . $typeName;
    }
}
