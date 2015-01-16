<?php

namespace SHyx0rmZ\ElasticaEntityMapping\Component;

/**
 * Class Watchdog
 * @package SHyx0rmZ\ElasticaEntityMapping\Component
 * @author Patrick Pokatilo <mail@shyxormz.net>
 */
class Watchdog
{
    /** @var array */
    private $mapping;
    /** @var string[] */
    private $indices;
    /** @var string */
    private $typeName;
    /** @var string */
    private $fileName;

    /**
     * @param string $typeName
     * @param array $mapping
     * @param array $indices
     * @param string $fileName
     */
    public function __construct($typeName, array $mapping, array $indices, $fileName)
    {
        $this->typeName = $typeName;
        $this->mapping = $mapping;
        $this->indices = $indices;
        $this->fileName = $fileName;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @return string[]
     */
    public function getIndices()
    {
        return $this->indices;
    }

    /**
     * @return array
     */
    public function getMapping()
    {
        return $this->mapping;
    }

    /**
     * @return string
     */
    public function getTypeName()
    {
        return $this->typeName;
    }
}
