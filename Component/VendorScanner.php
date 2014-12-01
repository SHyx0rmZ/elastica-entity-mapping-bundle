<?php

namespace SHyx0rmZ\ElasticaEntityMapping\Component;

/**
 * Class VendorScanner
 * @package SHyx0rmZ\ElasticaEntityMapping\Component
 * @author Patrick Pokatilo <mail@shyxormz.net>
 */
class VendorScanner
{
    /**
     * @var array
     */
    private $autoloadDirs = array('/include_paths.php', '/autoload_namespaces.php', '/autoload_psr4.php', '/autoload_classmap.php', '/autoload_files.php');

    /**
     * @return string[]
     */
    public function yieldIncludeDirectories()
    {
        $vendorDir = __DIR__ . '/../../..';

        foreach ($this->autoloadDirs as $autoloadDir) {
            $autoloadFile = $vendorDir . '/composer' . $autoloadDir;

            if (!is_file($autoloadFile)) {
                continue;
            }

            $autoloadMaps = require($autoloadFile);

            foreach ($autoloadMaps as $namespace => $autoloadMap) {
                $namespace = $this->normalizeNamespace($namespace);
                $autoloadMap = $this->normalizeAutoloadMap($autoloadMap);

                foreach ($autoloadMap as $includeDir) {
                    yield $namespace => $includeDir;
                }
            }
        }
    }

    /**
     * @param $namespace
     * @return string
     */
    private function normalizeNamespace($namespace)
    {
        if (!is_string($namespace)) {
            $namespace = '';
        }

        return $namespace;
    }

    /**
     * @param $autoloadMap
     * @return array
     */
    private function normalizeAutoloadMap($autoloadMap)
    {
        if (!is_array($autoloadMap)) {
            $autoloadMap = array($autoloadMap);
        }

        return $autoloadMap;
    }
}
