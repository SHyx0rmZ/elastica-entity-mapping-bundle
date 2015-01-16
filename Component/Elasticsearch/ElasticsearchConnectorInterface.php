<?php

namespace SHyx0rmZ\ElasticaEntityMapping\Component\Elasticsearch;

/**
 * Class ElasticsearchConnectorInterface
 * @package SHyx0rmZ\ElasticaEntityMapping\Component\Elasticsearch
 * @author Patrick Pokatilo <mail@shyxormz.net>
 */
interface ElasticsearchConnectorInterface
{
    /**
     * @param string $indexName
     * @return string
     */
    public function formatIndexAddress($indexName);

    /**
     * @param string $indexName
     * @param string $typeName
     * @return string
     */
    public function formatTypeAddress($indexName, $typeName);

    /**
     * @param string $indexName
     */
    public function closeIndex($indexName);

    /**
     * @param string $indexName
     */
    public function openIndex($indexName);

    /**
     * @param string $indexName
     * @param string $typeName
     * @return array
     */
    public function getMapping($indexName, $typeName);

    /**
     * @param string $indexName
     * @param string $typeName
     * @param array $mapping
     * @throws ElasticsearchException
     */
    public function setMapping($indexName, $typeName, array $mapping);

    /**
     * @param string $indexName
     * @param array $settings
     */
    public function setSettings($indexName, array $settings);
}
