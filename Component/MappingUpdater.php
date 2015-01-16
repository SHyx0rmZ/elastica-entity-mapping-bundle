<?php

namespace SHyx0rmZ\ElasticaEntityMapping\Component;

use Psr\Log\LoggerInterface;
use SHyx0rmZ\ElasticaEntityMapping\Component\Elasticsearch\IndexWrapper;
use SHyx0rmZ\ElasticaEntityMapping\Component\Elasticsearch\TypeWrapper;

/**
 * Class MappingUpdater
 * @package SHyx0rmZ\ElasticaEntityMapping\Component
 * @author Patrick Pokatilo
 */
class MappingUpdater
{
    /** @var LoggerInterface */
    private $logger;
    /** @var IndexWrapper */
    private $index;
    /** @var TypeWrapper */
    private $type;

    /**
     * @param LoggerInterface $logger
     * @param $type
     */
    public function __construct(LoggerInterface $logger, TypeWrapper $type)
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
        return $this->index->formatAddress();
    }

    /**
     * @return string
     */
    public function getTypeAddress()
    {
        return $this->type->formatAddress();
    }

    /**
     * @param array $mapping
     * @param array $settings
     */
    public function updateMapping(array $mapping, array $settings = array())
    {
        if ($this->updateType($mapping) !== true) {
            $this->logger->error('Error while updating elasticsearch mapping, trying to update settings');

            if ($settings != array()) {
                $this->updateIndex($settings);
            }

            if ($this->updateType($mapping) !== true) {
                throw new \RuntimeException('Error while updating elasticsearch mapping: ' . $this->getIndexAddress());
            }
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

        return $this->type->setMapping($mapping);
    }
}
