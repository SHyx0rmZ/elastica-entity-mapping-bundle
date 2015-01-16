<?php

namespace SHyx0rmZ\ElasticaEntityMapping\Component\Elasticsearch;

use Elastica\Client;
use Elastica\Exception\ResponseException;
use Elastica\Index;
use Elastica\Type;

class ElasticaAdapter implements ElasticsearchConnectorInterface
{
    const CLIENT_CLASS = Client::class;

    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @return string
     */
    public function formatIndexAddress($indexName)
    {
        $connection = $this->client->getConnection();

        return strtolower($connection->getTransport()) . '://' . $connection->getHost() . ':' . $connection->getPort() . '/' . $indexName;
    }

    /**
     * @return string
     */
    public function formatTypeAddress($indexName, $typeName)
    {
        return $this->formatIndexAddress($indexName) . '/' . $typeName;
    }

    public function closeIndex($indexName)
    {
        $index = new Index($this->client, $indexName);

        $index->close();
    }

    public function openIndex($indexName)
    {
        $index = new Index($this->client, $indexName);

        $index->open();
    }

    public function getMapping($indexName, $typeName)
    {
        $type = new Type(new Index($this->client, $indexName), $typeName);

        return $type->getMapping();
    }

    public function setMapping($indexName, $typeName, array $mapping)
    {
        $type = new Type(new Index($this->client, $indexName), $typeName);

        try {
            $type->setMapping($mapping);
        } catch (ResponseException $e) {
            var_dump($e);
            return false;
        }

        return true;
    }

    public function setSettings($indexName, array $settings)
    {
        $index = new Index($this->client, $indexName);

        $index->setSettings($settings);
    }
}
