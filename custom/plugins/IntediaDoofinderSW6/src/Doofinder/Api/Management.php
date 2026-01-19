<?php

namespace Intedia\Doofinder\Doofinder\Api;

use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class Management
{
    const API_VERSION = '2';
    const CLIENT_TIMEOUT = 5; // seconds
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
    public function __construct(
        SystemConfigService $systemConfigService,
        LoggerInterface $logger
    ) {
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
        if (($apiKey = $this->getConfig('apiKey', $context)) && ($apiDomain = $this->getConfig('apiDomain', $context))) {

            $apiInfo = explode('-', $apiKey);

            if (count($apiInfo) != 2) {
                return false;
            }

            $this->apiKey  = $apiInfo[1];
            $this->apiZone = $apiInfo[0];
            $this->baseUrl = sprintf("https://$apiDomain/api/v%s/", $this->apiZone, self::API_VERSION);

            return true;
        }

        return false;
    }

    public function createSearchEngine(?string $currency, ?string $language, string $name, ?string $siteUrl, ?bool $stopwords, ?bool $hasGrouping, string $storeId = '')
    {
        $body = [
            'currency'      => $currency,
            'language'      => $language,
            'name'          => $name,
            'site_url'      => $siteUrl,
            'stopwords'     => (bool) $stopwords,
            'has_grouping'  => (bool) $hasGrouping,
            'platform'      => 'shopware',
            'store_id'      => $storeId
        ];

        return $this->callToDoofinder(
            'search_engines',
            'POST',
            $body
        );
    }

    public function getSearchEngine(?string $hashId)
    {
        return $this->callToDoofinder(
            'search_engines/' . $hashId,
            'GET'
        );
    }

    public function getSearchEngines()
    {
        $searchEnginesCorrectIds = [];

        $searchEngines = $this->callToDoofinder(
            'search_engines/',
            'GET'
        );

        if (is_array($searchEngines)) {
            foreach ($searchEngines as $searchEngine) {
                if (isset($searchEngine['hashid'])) {
                    $searchEnginesCorrectIds[$searchEngine['hashid']] = $searchEngine;
                }
            }
        }

        return $searchEnginesCorrectIds;
    }

    public function deleteSearchEngine($hashId)
    {
        return $this->callToDoofinder(
            'search_engines/' . $hashId,
            'DELETE'
        );
    }

    public function createDoofinderSearchIndex($xmlFile, $hashId)
    {
        $body = [
            "name" => "product",
            "preset" => "product",
            "options" => [
                "exclude_out_of_stock_items" => false
            ],
            "datasources" => [
                [
                    "type" => "file",
                    "options" => [
                        "url" => $xmlFile
                    ]
                ]
            ]
        ];
        return $this->callToDoofinder(
            'search_engines/' . $hashId . '/indices',
            'POST',
            $body
        );
    }

    public function processDoofinderSearchIndex($hashId)
    {
        return $this->callToDoofinder(
            'search_engines/' . $hashId . '/_process',
            'POST'
        );
    }

    public function getProcessDoofinderSearchIndex($hashId)
    {
        return $this->callToDoofinder(
            'search_engines/' . $hashId . '/_process',
            'GET'
        );
    }

    public function callToDoofinder($endpoint, $method, $body = null)
    {
        try {
            if ($this->client) {
                if ($body) {
                    $response = $this->client->request($method, $endpoint,
                        [
                            'json' => $body,
                            'headers' => [
                                'Authorization' => 'Token ' . $this->apiKey
                            ]
                        ]
                    );
                } else {
                    $response = $this->client->request($method, $endpoint,
                        [
                            'headers' => [
                                'Authorization' => 'Token ' . $this->apiKey
                            ]
                        ]
                    );
                }

                if ($response->getStatusCode() === 200 || $response->getStatusCode() === 201) {

                    if ($this->getConfig('doofinderDebug')) {
                        $this->logger->error(json_encode([
                            'baseUrl'        => $this->baseUrl,
                            'endpoint'       => $endpoint,
                            'method'         => $method,
                            'authorization'  => 'Token ' . $this->apiKey,
                            'responseStatus' => $response->getStatusCode(),
                            'responseBody'   => json_encode(\GuzzleHttp\json_decode($response->getBody(), true))
                        ]));
                    }

                    return \GuzzleHttp\json_decode($response->getBody(), true);
                } else {
                    $this->logger->error(json_encode(['request' => $body, 'response' => $response]));
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
