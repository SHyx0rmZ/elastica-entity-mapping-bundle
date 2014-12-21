<?php

namespace SHyx0rmZ\ElasticaEntityMapping\Component;

use Elastica\Type;

class Mapping
{
    /** @var array */
    private $mapping;
    /** @var string */
    private $fileName;
    /** @var Type */
    private $type;

    public function __construct(array $mapping, $file, Type $type)
    {
        $this->mapping = $mapping;
        $this->fileName = $file;
        $this->type = $type;
    }

    public function getMapping()
    {
        return $this->mapping;
    }

    public function getFileName()
    {
        return $this->fileName;
    }

    public function getType()
    {
        return $this->type;
    }
}
