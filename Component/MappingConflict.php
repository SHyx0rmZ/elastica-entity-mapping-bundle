<?php

namespace SHyx0rmZ\ElasticaEntityMapping\Component;

class MappingConflict
{
    /** @var string */
    private $addressA;
    /** @var string */
    private $addressB;
    /** @var string */
    private $fileA;
    /** @var string */
    private $fileB;
    /** @var string */
    private $field;

    public function __construct($addressA, $addressB, $fileA, $fileB, $field)
    {
        $this->addressA = $addressA;
        $this->addressB = $addressB;
        $this->fileA = $fileA;
        $this->fileB = $fileB;
        $this->field = $field;
    }

    public function getMessage()
    {
        $message = 'Elasticsearch mapping conflict detected: ' . PHP_EOL;

        if ($this->addressA == $this->addressB) {
            $message .=
                '- address  : ' . $this->addressA . PHP_EOL .
                '- info     : Multiple field types on the same type in same index.' . PHP_EOL .
                '             You will not end up with the mapping you want.' . PHP_EOL;
        } else {
            $message .=
                '- address1 : ' . $this->addressA . PHP_EOL .
                '- address2 : ' . $this->addressB . PHP_EOL .
                '- info     : Multiple field types on similiarly named fields.' . PHP_EOL .
                '           : This will seriously screw up your searches.' . PHP_EOL;
        }

        $message .=
            '- file1    : ' . $this->fileA . PHP_EOL .
            '- file2    : ' . $this->fileB . PHP_EOL .
            '- field    : ' . $this->field;

        return $message;
    }
}
