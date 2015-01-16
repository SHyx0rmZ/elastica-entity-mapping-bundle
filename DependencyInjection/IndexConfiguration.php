<?php

namespace SHyx0rmZ\ElasticaEntityMapping\DependencyInjection;

/**
 * Class IndexConfiguration
 * @package SHyx0rmZ\ElasticaEntityMapping\DependencyInjection
 * @author Patrick Pokatilo <mail@shyxormz.net>
 */
class IndexConfiguration
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
     * @return string|null
     */
    public function getAlias()
    {
        return @$this->configuration['alias'];
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return @$this->configuration['name'];
    }

    /**
     * @return string|null
     */
    public function getPathToSettings()
    {
        return @$this->configuration['settings'];
    }

    /**
     * @return bool
     */
    public function hasAlias()
    {
        return $this->getAlias() != null;
    }

    /**
     * @return bool
     */
    public function hasName()
    {
        return $this->getName() != null;
    }

    /**
     * @return bool
     */
    public function hasPathToSettings()
    {
        return $this->getPathToSettings() != null;
    }
}
