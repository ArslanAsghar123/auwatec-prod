<?php

namespace Intedia\Doofinder\Doofinder\Api;

use GuzzleHttp\Client;
use Intedia\Doofinder\Core\Content\Settings\Service\SettingsHandler;
use Psr\Log\LoggerInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class Search
{
    const API_VERSION = '5';
    const CLIENT_TIMEOUT = 2.5; // seconds
    const CONFIG_PREFIX = 'IntediaDoofinderSW6.config.';
    const MAX_RESULTS = 1500;
    const PAGE_SIZE = 100;

    /** @var SystemConfigService */
    protected $systemConfigService;

    /** @var LoggerInterface */
    protected $logger;

    /** @var SettingsHandler $settingsHandler */
    protected SettingsHandler $settingsHandler;

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
     * @param SettingsHandler $settingsHandler
     */
    public function __construct(
        SystemConfigService $systemConfigService,
        LoggerInterface $logger,
        SettingsHandler $settingsHandler
    ) {
        $this->logger              = $logger;
        $this->systemConfigService = $systemConfigService;
        $this->settingsHandler     = $settingsHandler;

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
        if (($apiKey = $this->getConfig('apiKey', $context)) && ($searchDomain = $this->getConfig('searchDomain', $context))) {

            $apiInfo = explode('-', $apiKey);

            if (count($apiInfo) != 2) {
                return false;
            }

            $this->apiKey  = $apiInfo[1];
            $this->apiZone = $apiInfo[0];
            $this->baseUrl = sprintf("https://$searchDomain/%s/", $this->apiZone, self::API_VERSION);

            return true;
        }

        return false;
    }

    /**
     * @param $term
     * @param $context
     * @return array
     */
    public function queryIds($term, $context)
    {
        $this->context  = $context;
        $resultIds      = [];
        $page           = 1;
        $dfResponse     = $this->queryPage($term, $page, self::PAGE_SIZE);
        $productsToLoad = self::MAX_RESULTS;

        while ($dfResponse) {

            $dfResults = $dfResponse['results'];

            for ($i = 0; $i < count($dfResults) && ($productsToLoad > 0); $i++, --$productsToLoad) {

                if (array_key_exists('group_id', $dfResults[$i])) {
                    $resultIds[$dfResults[$i]['id']] = $dfResults[$i]['group_id'];
                } else {
                    $resultIds[] = $dfResults[$i]['id'];
                }
            }

            $dfResponse = $page * self::PAGE_SIZE < $dfResponse['total'] && $productsToLoad > 0 ? $this->queryPage($term, ++$page, self::PAGE_SIZE) : null;
        }

        return $resultIds;
    }

    /**
     * @param $term
     * @param $page
     * @param $rpp
     * @return mixed|null
     */
    protected function queryPage($term, $page, $rpp)
    {
        try {
            if ($this->client) {
                $doofinderLayer = $this->settingsHandler->getDoofinderLayer(
                    $this->settingsHandler->getDomain(
                        $this->context->getDomainId()
                    )
                );
                $response = $this->client->request('GET', 'search',
                    [
                        'query' => [
                            'hashid' => $doofinderLayer ? $doofinderLayer->getDooFinderHashId() : '',
                            'query' => $term,
                            'page' => $page,
                            'rpp' => $rpp
                        ],
                        'headers' => [
                            'Authorization' => 'Token ' . $this->apiKey
                        ]
                    ]
                );
                if ($response->getStatusCode() === 200) {

                    if ($this->getConfig('doofinderDebug')) {
                        $this->logger->error(json_encode([
                            'baseUrl'        => $this->baseUrl,
                            'endpoint'       => 'search',
                            'method'         => 'GET',
                            'authorization'  => 'Token ' . $this->apiKey,
                            'query'          => json_encode([
                                'hashid' => $doofinderLayer ? $doofinderLayer->getDooFinderHashId() : '',
                                'query' => $term,
                                'page' => $page,
                                'rpp' => $rpp
                            ]),
                            'responseStatus' => $response->getStatusCode(),
                            'responseBody'   => json_encode(\GuzzleHttp\json_decode($response->getBody(), true))
                        ]));
                    }

                    return \GuzzleHttp\json_decode($response->getBody(), true);
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