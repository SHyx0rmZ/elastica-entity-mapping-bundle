<?php

namespace SHyx0rmZ\ElasticaEntityMapping\Component;

use SHyx0rmZ\ElasticaEntityMapping\Component\Elasticsearch\TypeWrapper;

/**
 * Class Mapping
 * @package SHyx0rmZ\ElasticaEntityMapping\Component
 * @author Patrick Pokatilo <mail@shyxormz.net>
 */
class Mapping
{
    /** @var array */
    private $mapping;
    /** @var string */
    private $fileName;
    /** @var TypeWrapper */
    private $type;

    public function __construct(array $mapping, $file, TypeWrapper $type)
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
