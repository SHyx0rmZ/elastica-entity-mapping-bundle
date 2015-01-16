<?php

namespace SHyx0rmZ\ElasticaEntityMapping\DependencyInjection;

/**
 * Class ClientConfiguration
 * @package SHyx0rmZ\ElasticaEntityMapping\DependencyInjection
 * @author Patrick Pokatilo <mail@shyxormz.net>
 */
class ClientConfiguration
{
    /** @var array */
    private $configuration;

    /**
     * @param array $configuration
     */
    public function __construct(&$configuration)
    {
        $this->configuration = &$configuration;
    }

    /**
     * @return IndexConfiguration[]
     */
    public function getIndices()
    {
        if ($this->hasIndices()) {
            foreach ($this->configuration['indices'] as $indexConfiguration) {
                yield new IndexConfiguration($indexConfiguration);
            }
        }
    }

    /**
     * @return string|null
     */
    public function getServiceName()
    {
        return @$this->configuration['service'];
    }

    /**
     * @return bool
     */
    public function hasIndices()
    {
        return !empty(@$this->configuration['indices']);
    }

    /**
     * @return bool
     */
    public function hasServiceName()
    {
        return $this->getServiceName() != null;
    }

    /**
     * @return bool
     */
    public function shouldUpdate()
    {
        return @$this->configuration['update'] ?: false;
    }
}
