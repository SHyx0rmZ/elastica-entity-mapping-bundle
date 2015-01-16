<?php

namespace SHyx0rmZ\ElasticaEntityMapping\Component;

use SHyx0rmZ\ElasticaEntityMapping\Component\Elasticsearch\TypeWrapper;

/**
 * Class MappingConflictDetector
 * @package SHyx0rmZ\ElasticaEntityMapping\Component
 * @author Patrick Pokatilo <mail@shyxormz.net>
 */
class MappingConflictDetector
{
    /** @var Mapping[] */
    private $mappings = array();

    /**
     * @param Watchdog $watchdog
     * @param TypeWrapper $type
     */
    public function remember(Watchdog $watchdog, TypeWrapper $type)
    {
        $this->mappings[] = new Mapping(
            $watchdog->getMapping(),
            $watchdog->getFileName(),
            $type
        );
    }

    /**
     * @return MappingConflict|null
     */
    public function detectDifferentTypeConflict()
    {
        /* This is ugly, @fixme later */
        for ($i = 0; $i < count($this->mappings); ++$i) {
            for ($j = $i + 1; $j < count($this->mappings); ++$j) {
                $mappingA = $this->mappings[$i];
                $mappingB = $this->mappings[$j];
                $addressIndexA = $mappingA->getType()->getIndex()->formatAddress();
                $addressIndexB = $mappingB->getType()->getIndex()->formatAddress();

                if ($addressIndexA != $addressIndexB) {
                    continue;
                }

                $field = $this->compareFieldTypes($mappingA->getMapping(), $mappingB->getMapping());

                if ($field != '') {
                    return new MappingConflict(
                        $mappingA->getType()->formatAddress(),
                        $mappingB->getType()->formatAddress(),
                        $mappingA->getFileName(),
                        $mappingB->getFileName(),
                        $field
                    );
                }
            }
        }

        return null;
    }

    /**
     * @return MappingConflict|null
     */
    public function detectSameTypeConflict()
    {
        /** @var Mapping[] $resolvedMappings */
        $resolvedMappings = array();

        foreach ($this->mappings as $comparable) {
            $address = $comparable->getType()->getIndex()->formatAddress();

            if (isset($resolvedMappings[$address])
                && $resolvedMappings[$address]->getMapping() != $comparable->getMapping()) {
                $array1 = $resolvedMappings[$address]->getMapping();
                $array2 = $comparable->getMapping();

                $field = $this->compareFieldTypes($array1, $array2);

                return new MappingConflict(
                    $resolvedMappings[$address]->getType()->formatAddress(),
                    $comparable->getType()->formatAddress(),
                    $resolvedMappings[$address]->getFileName(),
                    $comparable->getFileName(),
                    $field
                );
            } else {
                $resolvedMappings[$address] = $comparable;
            }
        }

        return null;
    }

    /**
     * @param array $array1
     * @param array $array2
     * @param string $field
     * @return string
     */
    private function compareFieldTypes(array $array1, array $array2, $field = '')
    {
        foreach ($array1 as $key => $value) {
            if (!isset($array2[$key])) {
                break;
            }

            if (is_array($array1[$key]) && is_array($array2[$key])) {
                $result = $this->compareFieldTypes($array1[$key], $array2[$key], $key);

                if ($result != $key) {
                    return $result;
                }
            } elseif ($array1[$key] != $array2[$key]) {
                $field .= ($field ? '.' : '') . $key;
            }
        }

        return $field;
    }
}
