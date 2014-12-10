<?php

namespace SHyx0rmZ\ElasticaEntityMapping\Component;

use Elastica\Exception\ResponseException;
use Elastica\Index;
use Elastica\Type;
use Psr\Log\LoggerInterface;

/**
 * Class MappingUpdater
 * @package SHyx0rmZ\ElasticaEntityMapping\Component
 * @author Patrick Pokatilo
 */
class MappingUpdater
{
    /** @var LoggerInterface */
    private $logger;
    /** @var Index */
    private $index;
    /** @var Type */
    private $type;

    /**
     * @param LoggerInterface $logger
     * @param $type
     */
    public function __construct(LoggerInterface $logger, Type $type)
    {
        $this->logger = $logger;
        $this->index = $type->getIndex();
        $this->type = $type;
    }

    /**
     * @param array $mapping
     * @return bool
     */
    public function needsUpdate(array $mapping)
    {
        $currentMapping = $this->type->getMapping();
        $currentMapping = @$currentMapping[$this->index->getName()]['mappings'][$this->type->getName()]['properties'];

        return $currentMapping != $mapping;
    }

    /**
     * @return string
     */
    public function getIndexAddress()
    {
        return AddressFormatter::getIndexAddress($this->index);
    }

    /**
     * @return string
     */
    public function getTypeAddress()
    {
        return AddressFormatter::getTypeAddress($this->type);
    }

    /**
     * @param array $mapping
     * @param array $settings
     */
    public function updateMapping(array $mapping, array $settings = array())
    {
        try {
            $this->updateType($mapping);
        } catch (ResponseException $e) {
            $this->logger->error('Error while updating elasticsearch mapping, trying to update settings');

            if ($settings != array()) {
                $this->updateIndex($settings);
            }

            $this->updateType($mapping);
        }
    }

    /**
     * @param array $settings
     */
    private function updateIndex(array $settings)
    {
        $this->logger->info('Updating elasticsearch settings: ' . $this->getIndexAddress());
        $this->index->close();
        $this->index->setSettings($settings);
        $this->index->open();
    }

    /**
     * @param array $mapping
     */
    private function updateType(array $mapping)
    {
        $this->logger->info('Updating elasticsearch mapping: ' . $this->getTypeAddress());
        $this->type->setMapping($mapping);
    }
}
