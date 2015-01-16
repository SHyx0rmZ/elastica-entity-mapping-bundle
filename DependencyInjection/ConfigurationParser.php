<?php

namespace SHyx0rmZ\ElasticaEntityMapping\DependencyInjection;

/**
 * Class ConfigurationParser
 * @package SHyx0rmZ\ElasticaEntityMapping\DependencyInjection
 * @author Patrick Pokatilo <mail@shyxormz.net>
 */
class ConfigurationParser
{
    /** @var array */
    private $configuration;

    /**
     * @param array $configuration
     */
    public function __construct(array &$configuration)
    {
        $this->configuration = &$configuration;
    }

    /**
     * @return bool
     */
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
