<?php

namespace SHyx0rmZ\ElasticaEntityMapping\DependencyInjection;

class ClientConfiguration
{
    /** @var array */
    private $configuration;

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

    public function getServiceName()
    {
        return @$this->configuration['service'];
    }

    public function hasIndices()
    {
        return !empty(@$this->configuration['indices']);
    }

    public function hasServiceName()
    {
        return $this->getServiceName() != null;
    }

    public function shouldUpdate()
    {
        return @$this->configuration['update'] ?: false;
    }
}
