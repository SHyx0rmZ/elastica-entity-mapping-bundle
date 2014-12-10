<?php

namespace SHyx0rmZ\ElasticaEntityMapping\Component;

use Elastica\Index;
use Elastica\Type;

/**
 * Class AddressFormatter
 * @package SHyx0rmZ\ElasticaEntityMapping\Component
 * @author Patrick Pokatilo <mail@shyxormz.net>
 */
class AddressFormatter
{
    /**
     * @param Index $index
     * @return string
     */
    public static function getIndexAddress(Index $index)
    {
        $indexName = $index->getName();
        $client = $index->getClient();
        $connection = $client->getConnection();

        return strtolower($connection->getTransport()) . '://' . $connection->getHost() . ':' . $connection->getPort() . '/' . $indexName;
    }

    /**
     * @param Type $type
     * @return string
     */
    public static function getTypeAddress(Type $type)
    {
        $typeName = $type->getName();

        return self::getIndexAddress($type->getIndex()) . '/' . $typeName;
    }
}
