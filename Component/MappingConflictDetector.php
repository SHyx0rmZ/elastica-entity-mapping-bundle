<?php

namespace SHyx0rmZ\ElasticaEntityMapping\Component;

use Elastica\Type;
use Psr\Log\LoggerInterface;

/**
 * Class MappingConflictDetector
 * @package SHyx0rmZ\ElasticaEntityMapping\Component
 * @author Patrick Pokatilo <mail@shyxormz.net>
 */
class MappingConflictDetector
{
    /** @var LoggerInterface */
    private $logger;
    /** @var array */
    private $mappings = array();

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param Watchdog $watchdog
     * @param Type $type
     */
    public function remember(Watchdog $watchdog, Type $type)
    {
        $this->mappings[] = (object)array(
            'mapping' => $watchdog->getMapping(),
            'file' => $watchdog->getFileName(),
            'type' => $type
        );
    }

    /**
     * @return array|null
     */
    public function detectConflict()
    {
        $resolvedMappings = array();

        foreach ($this->mappings as $comparable) {
            $indexAddress = AddressFormatter::getIndexAddress($comparable->type->getIndex());
            $typeAddress = AddressFormatter::getTypeAddress($comparable->type);

            if (!isset($resolvedMappings)) {
                $resolvedMappings[$indexAddress] = array();
            }

            if (isset($resolvedMappings[$indexAddress][$typeAddress])
                && $resolvedMappings[$indexAddress][$typeAddress][0] != $comparable->mapping) {
                $array1 = $resolvedMappings[$indexAddress][$typeAddress][0];
                $array2 = $comparable->mapping;

                $field = $this->compare($array1, $array2);

                return array(
                    'address' => $typeAddress,
                    'file1' => $resolvedMappings[$indexAddress][$typeAddress][1],
                    'file2' => $comparable->file,
                    'field' => $field
                );
            } else {
                $resolvedMappings[$indexAddress][$typeAddress] = [ $comparable->mapping, $comparable->file ];
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
    private function compare(array $array1, array $array2, $field = '')
    {
        foreach ($array1 as $key => $value) {
            if (!isset($array2[$key])) {
                break;
            }

            if (is_array($array1[$key]) && is_array($array2[$key])) {
                $field = $this->compare($array1[$key], $array2[$key], $key);

                if ($field != $key) {
                    break;
                }
            } elseif ($array1[$key] != $array2[$key]) {
                $field .= ($field ? '.' : '') . $key;
            }
        }

        return $field;
    }
}
