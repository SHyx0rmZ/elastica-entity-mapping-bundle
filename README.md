elastica-entity-mapping-bundle
==============================
[![Latest Stable Version](http://poser.services.witches.io/ppokatilo/elastica-entity-mapping-bundle/v/stable.svg)](https://packagist.org/packages/ppokatilo/elastica-entity-mapping-bundle)
[![Total Downloads](http://poser.services.witches.io/ppokatilo/elastica-entity-mapping-bundle/downloads.svg)](https://packagist.org/packages/ppokatilo/elastica-entity-mapping-bundle)
[![Latest Unstable Version](http://poser.services.witches.io/ppokatilo/elastica-entity-mapping-bundle/v/unstable.svg)](https://packagist.org/packages/ppokatilo/elastica-entity-mapping-bundle)
[![License](http://poser.services.witches.io/ppokatilo/elastica-entity-mapping-bundle/license.svg)](https://packagist.org/packages/ppokatilo/elastica-entity-mapping-bundle)

An Symfony2 bundle that automatically updates your Elasticsearch mappings or notifies you of changes.

## How it works
An elastica client service is modified to be constructed using a factory. The factory reads Composer's
autoload files to know about all your dependencies. It will then scan each directory for a subdirectory
called `Entity` and search the PHP files in that subdirectory for the `@ElasticsearchMapping` annotation.

When instantiating the elastica client service, the factory will first check if the mapping of registered
entities on disk differs from that in Elasticsearch. If so, it will either throw an exception or try to
update the mapping automatically.

## Example usage

- app/AppKernel.php

  ```php
  class AppKernel extends Kernel
  {
    public function registerBundles()
    {
        $bundles = array(
          // ...
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new SHyx0rmZ\ElasticaEntityMapping\ElasticaEntityMappingBundle();
        }

        return $bundles;
    }
  }
  
  // ...
  ```

- app/config/config_dev.yml

  ```yml
  elastica_entity_mapping:
    client: elastica.client
    update: false
    indices:
      -
        name: %elastica_index_name%
        alias: dev
        settings: vendor/example/entitybundle/settings.json
      -
        name: my_other_index
        alias: other
  ```

- vendor/example/entitybundle/ExampleEntity.php

  ```php
  /**
   * @ElasticsearchMapping(file="./example_entity.json", indices="dev,other")
   **/
  class ExampleEntity
  {
    // ...
  }
  ```
