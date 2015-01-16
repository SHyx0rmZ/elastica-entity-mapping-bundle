<?php

namespace SHyx0rmZ\ElasticaEntityMapping\Component\Elasticsearch;

use Elastification\Client\Client;
use Elastification\Client\Exception\ClientException;
use Elastification\Client\Exception\ResponseException;
use Elastification\Client\Request\V1x\Index\CloseIndexRequest;
use Elastification\Client\Request\V1x\Index\CreateMappingRequest;
use Elastification\Client\Request\V1x\Index\GetMappingRequest;
use Elastification\Client\Request\V1x\Index\UpdateIndexSettingsRequest;
use Elastification\Client\Request\V1x\Index\OpenIndexRequest;
use Elastification\Client\Serializer\NativeJsonSerializer;

class ElastificationAdapter implements ElasticsearchConnectorInterface
{
    const CLIENT_CLASS = Client::class;

    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function formatIndexAddress($indexName)
    {
        return '/' . $indexName;
    }

    public function formatTypeAddress($indexName, $typeName)
    {
        return $this->formatIndexAddress($indexName) . '/' . $typeName;
    }

    public function closeIndex($indexName)
    {
        $request = new CloseIndexRequest($indexName, null, new NativeJsonSerializer());

        $this->client->send($request);
    }

    public function openIndex($indexName)
    {
        $request = new OpenIndexRequest($indexName, null, new NativeJsonSerializer());

        $this->client->send($request);
    }

    public function getMapping($indexName, $typeName)
    {
        $request = new GetMappingRequest($indexName, $typeName, new NativeJsonSerializer());

        $response = $this->client->send($request);

        return $response->getData()->getGatewayValue();
    }

    public function setMapping($indexName, $typeName, array $mapping)
    {
        $request = new CreateMappingRequest($indexName, $typeName, new NativeJsonSerializer(), array('force_object' => true));

        $mapping = array(
            $typeName => array(
                'properties' => $mapping
            )
        );

        $request->setBody($mapping);

        try {
            $this->client->send($request);
        } catch (ClientException $e) {
            throw new ElasticsearchException($e->getMessage(), $e->getCode());
        }
    }

    public function setSettings($indexName, array $settings)
    {
        $request = new UpdateIndexSettingsRequest($indexName, null, new NativeJsonSerializer(), array(), array('force_object' => true));

        $request->setBody($settings);

        $this->client->send($request);
    }
}
