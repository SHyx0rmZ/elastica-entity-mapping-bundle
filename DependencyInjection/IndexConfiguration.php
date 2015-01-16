<?php

namespace SHyx0rmZ\ElasticaEntityMapping\DependencyInjection;

class IndexConfiguration
{
    /** @var array */
    private $configuration;

    public function __construct(&$configuration)
    {
        $this->configuration = &$configuration;
    }

    public function getAlias()
    {
        return @$this->configuration['alias'];
    }

    public function getName()
    {
        return @$this->configuration['name'];
    }

    public function getPathToSettings()
    {
        return @$this->configuration['settings'];
    }

    public function hasAlias()
    {
        return $this->getAlias() != null;
    }

    public function hasName()
    {
        return $this->getName() != null;
    }

    public function hasPathToSettings()
    {
        return $this->getPathToSettings() != null;
    }
}
