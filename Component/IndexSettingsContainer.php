<?php

namespace SHyx0rmZ\ElasticaEntityMapping\Component;

use SHyx0rmZ\ProjectScanner\Util\Util;

/**
 * Class IndexSettingContainer
 * @package SHyx0rmZ\ElasticaEntityMapping\Component
 * @author Patrick Pokatilo <mail@shyxormz.net>
 */
class IndexSettingsContainer
{
    /** @var array */
    private $config;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->config['name'];
    }

    /**
     * @return string|null
     */
    public function getAlias()
    {
        return @$this->config['alias'];
    }

    /**
     * @return array
     */
    public function getSettings()
    {
        $settings = @$this->config['settings'];

        if ($settings !== null) {
            $file = Util::modifyPath(__DIR__, '../../../../' . $settings);

            if (!is_file($file)) {
                throw new \RuntimeException('ElasticsearchMapping file "' . $file . '" not found');
            }

            $settings = json_decode(file_get_contents($file), true);
        }

        return $settings ?: array();
    }
}
