<?php

namespace SHyx0rmZ\ElasticaEntityMapping\Component\Elasticsearch;

/**
 * Class ElasticsearchConnectorFactory
 * @package SHyx0rmZ\ElasticaEntityMapping\Component\Elasticsearch
 * @author Patrick Pokatilo <mail@shyxormz.net>
 */
class ElasticsearchConnectorFactory
{
    /**
     * @param string $clientClass
     * @param object $client
     * @return ElasticsearchConnectorInterface
     * @throws \UnexpectedValueException
     */
    public static function createConnector($clientClass, $client)
    {
        switch ($clientClass) {
            case ElasticaAdapter::CLIENT_CLASS:
                return new ElasticaAdapter($client);
            case ElastificationAdapter::CLIENT_CLASS:
                return new ElastificationAdapter($client);
        }

        throw new \UnexpectedValueException('Unknown Elasticsearch provider');
    }

    public static function createIndexWrapper($clientClass, $client, $indexName)
    {
        return self::createIndexWrapperFromConnector(self::createConnector($clientClass, $client), $indexName);
    }

    public static function createIndexWrapperFromConnector(ElasticsearchConnectorInterface $connector, $indexName)
    {
        return new IndexWrapper($connector, $indexName);
    }

    public static function createTypeWrapper($clientClass, $client, $indexName, $typeName)
    {
        return self::createIndexWrapper($clientClass, $client, $indexName)->getType($typeName);
    }

    public static function createTypeWrapperFromConnector(ElasticsearchConnectorInterface $connector, $indexName, $typeName)
    {
        return self::createIndexWrapperFromConnector($connector, $indexName)->getType($typeName);
    }
}
