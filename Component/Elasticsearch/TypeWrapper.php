<?php

namespace SHyx0rmZ\ElasticaEntityMapping\Component\Elasticsearch;

class TypeWrapper
{
    private $index;
    private $name;
    private $connector;

    public function __construct(IndexWrapper $index, $name)
    {
        $this->connector = $index->getConnector();
        $this->index = $index;
        $this->name = $name;
    }

    public function formatAddress()
    {
        return $this->connector->formatTypeAddress($this->index->getName(), $this->getName());
    }

    public function getMapping()
    {
        return $this->connector->getMapping($this->index->getName(), $this->getName());
    }

    public function setMapping(array $mapping)
    {
        return $this->connector->setMapping($this->index->getName(), $this->getName(), $mapping);
    }

    public function getConnector()
    {
        return $this->connector;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getIndex()
    {
        return $this->index;
    }
}
