<?php

namespace SHyx0rmZ\ElasticaEntityMapping\DependencyInjection;

use SHyx0rmZ\ProjectScanner\Util\Util;

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

    /**
     * @return array
     */
    public function getSettings()
    {
        $settings = $this->getPathToSettings();

        if ($this->hasPathToSettings()) {
            $file = Util::modifyPath(__DIR__, '../../../../' . $settings);

            if (!is_file($file)) {
                throw new \RuntimeException('ElasticsearchMapping file "' . $file . '" not found');
            }

            $settings = json_decode(file_get_contents($file), true);
        }

        return $settings ?: array();
    }
}
