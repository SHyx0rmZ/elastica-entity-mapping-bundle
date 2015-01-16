<?php

namespace SHyx0rmZ\ElasticaEntityMapping\Component\Elasticsearch;

use Elastica\Client;
use Elastica\Exception\ResponseException;
use Elastica\Index;
use Elastica\Type;

/**
 * Class ElasticaAdapter
 * @package SHyx0rmZ\ElasticaEntityMapping\Component\Elasticsearch
 * @author Patrick Pokatilo <mail@shyxormz.net>
 */
class ElasticaAdapter implements ElasticsearchConnectorInterface
{
    const CLIENT_CLASS = Client::class;

    /** @var Client */
    private $client;

    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @inheritdoc
     */
    public function formatIndexAddress($indexName)
    {
        $connection = $this->client->getConnection();

        return strtolower($connection->getTransport()) . '://' . $connection->getHost() . ':' . $connection->getPort() . '/' . $indexName;
    }

    /**
     * @inheritdoc
     */
    public function formatTypeAddress($indexName, $typeName)
    {
        return $this->formatIndexAddress($indexName) . '/' . $typeName;
    }

    /**
     * @inheritdoc
     */
    public function closeIndex($indexName)
    {
        $index = new Index($this->client, $indexName);

        $index->close();
    }

    /**
     * @inheritdoc
     */
    public function openIndex($indexName)
    {
        $index = new Index($this->client, $indexName);

        $index->open();
    }

    /**
     * @inheritdoc
     */
    public function getMapping($indexName, $typeName)
    {
        $type = new Type(new Index($this->client, $indexName), $typeName);

        return $type->getMapping();
    }

    /**
     * @inheritdoc
     */
    public function setMapping($indexName, $typeName, array $mapping)
    {
        $type = new Type(new Index($this->client, $indexName), $typeName);

        try {
            $type->setMapping($mapping);
        } catch (ResponseException $e) {
            return false;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function setSettings($indexName, array $settings)
    {
        $index = new Index($this->client, $indexName);

        $index->setSettings($settings);
    }
}
