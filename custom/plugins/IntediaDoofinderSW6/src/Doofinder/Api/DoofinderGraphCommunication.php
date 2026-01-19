<?php

namespace Intedia\Doofinder\Doofinder\Api;

use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class DoofinderGraphCommunication
{
    const API_VERSION = '1';
    const CLIENT_TIMEOUT = 10; // seconds
    const CONFIG_PREFIX = 'IntediaDoofinderSW6.config.';

    /** @var SystemConfigService */
    protected $systemConfigService;

    /** @var LoggerInterface */
    protected $logger;

    /** @var Client */
    protected $client;

    /** @var string */
    protected $baseUrl;

    /** @var string */
    protected $apiKey;

    /** @var string */
    protected $apiZone;

    /**
     * Search constructor.
     *
     * @param SystemConfigService $systemConfigService
     * @param LoggerInterface $logger
     */
    public function __construct(SystemConfigService $systemConfigService, LoggerInterface $logger)
    {
        $this->logger              = $logger;
        $this->systemConfigService = $systemConfigService;

        if ($this->initConfig()) {

            $this->client = new Client([
                'base_uri' => $this->baseUrl,
                'timeout' => self::CLIENT_TIMEOUT
            ]);
        }
    }

    /**
     * Initializes api with config values
     * @param SalesChannelContext|null $context
     * @return bool
     */
    protected function initConfig(?SalesChannelContext $context = null): bool
    {
        if (($apiKey = $this->getConfig('apiKey', $context)) && ($adminDomain = $this->getConfig('adminDomain', $context))) {

            $apiInfo = explode('-', $apiKey);

            if (count($apiInfo) != 2) {
                return false;
            }

            $this->apiKey  = $apiInfo[1];
            $this->apiZone = $apiInfo[0];
            $this->baseUrl = sprintf("https://$adminDomain/api/v%s/", $this->apiZone, self::API_VERSION);

            return true;
        }

        return false;
    }

    public function getStore($id)
    {
        $body = '{
           installation_by_id(id: "' . $id . '")
           {
               id
               name
               config
           }
        }';

        return $this->callToDoofinder(
            'graphql.json',
            'query',
            $body
        );
    }

    public function createStore($title, $trigger, $currency, $language, $storeUrl, $searchEngineXml, $searchEngineName)
    {
        $body = '{
           create_store(
               store_fields:{name: "' . $title . '", platform: "shopware", primary_language: "' . $language . '", site_url:"' . $storeUrl . '", query_input:"' . $trigger . '"},
               search_engine_fields:[
                   {
                        name: "' . $searchEngineName . '", language: "'. $language . '", currency: "'.$currency.'", feed_url: "' . $searchEngineXml . '"}
                   ]
               )
           {
               installation_id
               default_search_engine
               search_engines
               script
           }
        }
        ';

        return $this->callToDoofinder(
            'graphql.json',
            'mutation',
            $body
        );
    }

    public function editStore($storeId, $title, $config)
    {
        $body = '{
           update_installation(id: "' . $storeId . '", 
           update_fields: 
           {
               name: "' . $title . '",
               config: "'. $config . '"

            })
           {
               installation{name}
           }
        }';

        return $this->callToDoofinder(
            'graphql.json',
            'mutation',
            $body
        );
    }

    public function deleteStore($storeId)
    {
        $body = '{
           delete_store(id: "' . $storeId . '")
           {
               id
               deleted
           }
        }';

        return $this->callToDoofinder(
            'graphql.json',
            'mutation',
            $body
        );
    }

    public function getStores()
    {
        $body = "{
            stores_by_token {
                id
                name
            }}
        ";

        return $this->callToDoofinder(
            'graphql.json',
            'query',
            $body
        );
    }

    public function callToDoofinder($endpoint, $method, $body)
    {
        try {
            if ($this->client) {
                $response = $this->client->post($endpoint,
                    [
                        'body' => $method . ' ' . $body,
                        'headers' => [
                            'Authorization' => 'Token ' . $this->apiKey
                        ]
                    ]
                );

                if ($response->getStatusCode() === 200 || $response->getStatusCode() === 201) {

                    if ($this->getConfig('doofinderDebug')) {
                        $this->logger->error(json_encode([
                            'url'            => $this->baseUrl . $endpoint,
                            'request'        => $method . ' ' . $body,
                            'authorization'  => 'Token ' . $this->apiKey,
                            'responseStatus' => $response->getStatusCode(),
                            'responseBody'   => json_encode(\GuzzleHttp\json_decode($response->getBody(), true))
                        ]));
                    }

                    return \GuzzleHttp\json_decode($response->getBody(), true);
                } else {
                    $this->logger->error(json_encode(['request' => $method . ' ' . $body, 'response' => $response]));
                }
            }
        } catch (\Exception $e) {
            $this->logger->error("Exception receiving results from doofinder: " . $e->getMessage());
        }

        return null;
    }

    /**
     * @param $configKey
     * @param null $context
     * @return mixed|null
     */
    protected function getConfig($configKey, $context = null)
    {
        try {

            $pluginConfig = $this->systemConfigService->getDomain(self::CONFIG_PREFIX, $context ? $context->getSalesChannel()->getId() : null, true);
            $configKey    = self::CONFIG_PREFIX . $configKey;

            if ($pluginConfig && array_key_exists($configKey, $pluginConfig)) {
                return $pluginConfig[$configKey];
            }
        }
        catch (\Exception $e) {
            $this->logger->error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }

        return null;
    }
}
