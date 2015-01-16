<?php

namespace SHyx0rmZ\ElasticaEntityMapping\Component\Elasticsearch;

class IndexWrapper
{
    private $connector;
    private $name;

    public function __construct(ElasticsearchConnectorInterface $connector, $name)
    {
        $this->connector = $connector;
        $this->name = $name;
    }

    public function open()
    {
        $this->connector->openIndex($this->getName());
    }

    public function close()
    {
        $this->connector->closeIndex($this->getName());
    }

    public function setSettings(array $settings)
    {
        $this->connector->setSettings($this->getName(), $settings);
    }

    public function formatAddress()
    {
        return $this->connector->formatIndexAddress($this->getName());
    }

    public function getConnector()
    {
        return $this->connector;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getType($typeName)
    {
        return new TypeWrapper($this, $typeName);
    }
}
