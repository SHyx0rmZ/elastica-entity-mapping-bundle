<?php

namespace SHyx0rmZ\ElasticaEntityMapping\Component;

class MappingConflict
{
    /** @var string */
    private $address;
    /** @var string */
    private $fileA;
    /** @var string */
    private $fileB;
    /** @var string */
    private $field;

    public function __construct($address, $fileA, $fileB, $field)
    {
        $this->address = $address;
        $this->fileA = $fileA;
        $this->fileB = $fileB;
        $this->field = $field;
    }

    public function getMessage()
    {
        return 'Elasticsearch mapping conflict detected: ' . PHP_EOL
        . '- address : ' . $this->getAddress() . PHP_EOL
        . '- file1   : ' . $this->getFileA() . PHP_EOL
        . '- file2   : ' . $this->getFileB() . PHP_EOL
        . '- field   : ' . $this->getField();
    }

    private function getAddress()
    {
        return $this->address;
    }

    private function getFileA()
    {
        return $this->fileA;
    }

    private function getFileB()
    {
        return $this->fileB;
    }

    private function getField()
    {
        return $this->field;
    }
}
