<?php

namespace SHyx0rmZ\ElasticaEntityMapping\DependencyInjection;

class ConfigurationParser
{
    /** @var array */
    private $configuration;

    public function __construct(array &$configuration)
    {
        $this->configuration = &$configuration;
    }

    public function hasClients()
    {
        return !empty(@$this->configuration['clients']);
    }

    /**
     * @return ClientConfiguration[]
     */
    public function getClients()
    {
        if ($this->hasClients()) {
            foreach ($this->configuration['clients'] as $clientConfiguration) {
                yield new ClientConfiguration($clientConfiguration);
            }
        }
    }
}
