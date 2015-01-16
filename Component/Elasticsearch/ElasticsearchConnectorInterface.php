<?php

namespace SHyx0rmZ\ElasticaEntityMapping\Component\Elasticsearch;

interface ElasticsearchConnectorInterface
{
    public function formatIndexAddress($indexName);
    public function formatTypeAddress($indexName, $typeName);
    public function closeIndex($indexName);
    public function openIndex($indexName);
    public function getMapping($indexName, $typeName);
    public function setMapping($indexName, $typeName, array $mapping);
    public function setSettings($indexName, array $settings);
}
