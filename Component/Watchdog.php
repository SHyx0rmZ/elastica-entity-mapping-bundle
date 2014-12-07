<?php

namespace SHyx0rmZ\ElasticaEntityMapping\Component;

class Watchdog
{
    private $mapping;
    private $indices;
    private $type;

    public function __construct($type, $mapping, $indices)
    {
        $this->type = $type;
        $this->mapping = $mapping;
        $this->indices = $indices;
    }

    public function getIndices()
    {
        return $this->indices;
    }

    public function getMapping()
    {
        return $this->mapping;
    }

    public function getType()
    {
        return $this->type;
    }
}
