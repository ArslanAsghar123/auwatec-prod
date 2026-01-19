<?php declare(strict_types=1);

namespace Intedia\Doofinder\Core\Content\Settings\Service;

use DateTimeImmutable;
use Intedia\Doofinder\Core\Content\ProductExport\Service\ExportHandler;
use Intedia\Doofinder\Custom\DooFinderLayerEntity;
use Intedia\Doofinder\Doofinder\Api\DoofinderGraphCommunication;
use Intedia\Doofinder\Doofinder\Api\Management;
use Shopware\Core\Content\ProductExport\ProductExportEntity;
use Shopware\Core\Defaults;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainEntity;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;

/**
 * @package Intedia\Doofinder\Core\Content\ProductExport\Service
 */
class CommunicationHandler
{
    /** @var DoofinderGraphCommunication $doofinderGraphCommunication */
    protected DoofinderGraphCommunication $doofinderGraphCommunication;

    /** @var Management $management */
    protected Management $management;

    /** @var SettingsHandler $settingsHandler */
    protected SettingsHandler $settingsHandler;

    /** @var ExportHandler $exportHandler */
    protected ExportHandler $exportHandler;

    public function __construct(
        Management                  $management,
        DoofinderGraphCommunication $doofinderGraphCommunication,
        SettingsHandler             $settingsHandler,
        ExportHandler               $exportHandler
    ) {
        $this->management                   = $management;
        $this->doofinderGraphCommunication  = $doofinderGraphCommunication;
        $this->settingsHandler              = $settingsHandler;
        $this->exportHandler                = $exportHandler;
    }

    /**
     * Returns all DooFinder Stores from the DooFinder Api
     *
     * @return array
     */
    public function doofinderStores(): array
    {
        $stores = $this->doofinderGraphCommunication->getStores()['data']['stores_by_token'];
        $searchEngines = $this->management->getSearchEngines();

        $languages = $this->settingsHandler->getLanguages();
        $currencies = $this->settingsHandler->getCurrencies();
        foreach ($stores as $index => $store) {
            $storeInfo = $this->doofinderStore($store['id'])[0];
            $defaults = [];
            if (isset($storeInfo['config']['defaults'])) {
                $defaults = $storeInfo['config']['defaults'];
            }

            $standardSearchEngine = [];
            if (isset($defaults['hashid']) &&
                isset($searchEngines[$defaults['hashid']])
            ) {
                $standardSearchEngine = $searchEngines[$defaults['hashid']];
            }

            $outputSearchEngines = [];
            foreach ($searchEngines as $searchEngine) {
                if ($searchEngine['store_id'] == $store['id']) {
                    $outputSearchEngines[] = [
                        'id' => $searchEngine['hashid'],
                        'name' => $searchEngine['name']
                    ];
                }
            }

            $stores[$index]['languages'] = $languages;
            $stores[$index]['currencies'] = $currencies;
            $stores[$index]['storeInfo'] = $storeInfo;
            $stores[$index]['standardSearchEngine'] = $standardSearchEngine;
            $stores[$index]['searchEngines'] = $outputSearchEngines;
        }

        return (array) $stores;
    }

    /**
     * Returns Specific DooFinder Store from the DooFinder Api
     *
     * @param $id
     * @return array
     */
    public function doofinderStore($id): array
    {
        return $this->doofinderGraphCommunication->getStore($id)['data']['installation_by_id'];
    }

    /**
     * Creates the Shopware Product Export for DooFinder
     *
     * @param SalesChannelEntity $storeFrontChannel
     * @param SalesChannelDomainEntity $domain
     * @param $storeId
     * @param $hashId
     * @return void
     */
    public function createDoofinderExport(SalesChannelEntity $storeFrontChannel, SalesChannelDomainEntity $domain, $storeId, $hashId = null): void
    {
        $name = "DF – {$storeFrontChannel->getName()} ({$domain->getLanguage()->getName()})";

        if ($hashId) {
            $doofinderCommunication = $this->management->getSearchEngine(
                $hashId
            );
        } else {
            $doofinderCommunication = $this->management->createSearchEngine(
                strtoupper($domain->getCurrency()->getIsoCode()),
                strtolower(explode('-', $domain->getLanguage()->getLocale()->getCode())[0]),
                $name,
                $domain->getUrl(),
                false,
                false,
                $storeId
            );
        }

        if (isset($doofinderCommunication['hashid'])) {
            $this->settingsHandler->createDoofinderEntity($doofinderCommunication['hashid'], $storeId, $domain->getId(), $name, $storeFrontChannel);
        }
    }

    /**
     * Creates a SearchIndex at DooFinder with the Shopware ProductExport
     *
     * @param SalesChannelEntity $doofinderStoreFrontChannel
     * @param SalesChannelDomainEntity $domain
     * @return void
     */
    public function createDooFinderSearchIndex(SalesChannelEntity $doofinderStoreFrontChannel, SalesChannelDomainEntity $domain): void
    {
        /** @var DooFinderLayerEntity $doofinderLayer */
        $doofinderLayer = $this->settingsHandler->getDoofinderLayer($domain);

        if ($doofinderLayer) {
            $productExport = $doofinderStoreFrontChannel->getProductExports()->first();
            $url           = $domain->getUrl() . '/store-api/product-export/' . $productExport->getAccessKey() . '/' . $productExport->getFileName();

            $this->management->createDoofinderSearchIndex(
                $url,
                $doofinderLayer->getDooFinderHashId(),
            );
        }
    }

    /**
     * Updates the DooFinder Store at DooFinder itself.
     * All Data needs to be within edit call. Even Data that is not edited in any way.
     * Edits the whole DooFinder Store at the Api
     *
     * @param $storeId
     * @param $domainId
     * @param $title
     * @param $trigger
     * @return array
     * @throws \Exception
     */
    public function editDoofinderStore(
        $storeId,
        $domainId,
        $title = null,
        $trigger = null,
        $hashId = null
    ): array {
        $changeDefaults = false;
        if ($title) {
            $changeDefaults = true;
        }

        $storeData = $this->doofinderStore($storeId)[0];
        if (!$title) {
            $title = $storeData['name'];
        }
        if (!$trigger) {
            $trigger = $storeData['config']['trigger'];
        }
        $domain = $this->settingsHandler->getDomain($domainId);

        $currency = $domain->getCurrency()->getIsoCode();
        $language = $domain->getLanguage()->getLocale()->getCode();

        /** @var DooFinderLayerEntity $doofinderEntity */
        $doofinderEntity = $this->settingsHandler->getDoofinderLayer($domain);
        $doofinderHashId = $hashId;
        if (isset($doofinderEntity) && $doofinderEntity->getDooFinderHashId() != '') {
            $doofinderHashId = $doofinderEntity->getDooFinderHashId();
        }

        if (($doofinderEntity && $doofinderEntity->getDoofinderStoreId()) && $doofinderEntity->getDoofinderStoreId() != $storeId) {
            return ['error' => 'existsAlready'];
        }
        if (
            $storeData['config']['defaults']['language'] == $language &&
            $storeData['config']['defaults']['currency'] == $currency &&
            $storeData['id'] == $storeId &&
            $storeData['config']['defaults']['hashid'] != $doofinderHashId
        ) {
            return ['error' => 'languageAndCurrencyAlreadyInUse'];
        }

        $store['config']['defaults']['currency'] = $currency;
        $store['config']['defaults']['language'] = $language;
        $store['config']['search_engines'] = $storeData['config']['search_engines'];
        if ($changeDefaults) {
            if ($doofinderEntity) {
                $store['config']['defaults']['hashid'] = $doofinderEntity->getDooFinderHashId();
            } else {
                $this->createDoofinderExportForDomainIfRequired(
                    $this->settingsHandler->getStorefrontChannel($domain->getSalesChannelId()),
                    $this->settingsHandler->getDomain($domainId),
                    $storeId
                );

                $newDoofinderEntity = $this->settingsHandler->getDoofinderLayer($domain);
                if ($newDoofinderEntity) {
                    $store['config']['defaults']['hashid'] = $newDoofinderEntity->getDooFinderHashId();
                }
            }
        } else {
            $store['config']['defaults'] = $storeData['config']['defaults'];
        }

        $searchEngines = $this->settingsHandler->getDoofinderLayersByStoreId($storeId);
        foreach ($searchEngines->getEntities()->getElements() as $searchEngine) {
            $domain = $this->settingsHandler->getDomain($searchEngine->getDomainId());
            $store['config']['search_engines'][$domain->getLanguage()->getLocale()->getCode()][$domain->getCurrency()->getIsoCode()] =
                $searchEngine->getDooFinderHashId();
        }

        $store['config']['trigger'] = $trigger;

        $config = addslashes(json_encode($store['config'], JSON_UNESCAPED_UNICODE));

        return $this->doofinderGraphCommunication->editStore($storeId, $title, $config);
    }

    /**
     * @param $domainId
     * @param $title
     * @param $trigger
     * @return array
     * @throws \Exception
     */
    public function createDoofinderStore($domainId, $title, $trigger): array
    {
        $domain = $this->settingsHandler->getDomain($domainId);
        $currency = $domain->getCurrency()->getIsoCode();
        $language = $domain->getLanguage()->getLocale()->getCode();

        $doofinderStoreFrontChannel = $this->settingsHandler->getSalesChannelByDomain($domain);
        $searchEngine = $this->createDoofinderExportForDomainIfRequired($doofinderStoreFrontChannel, $domain);

        $productExport = $searchEngine->getProductExports()->first();
        $searchEngineXml = $domain->getUrl() . '/store-api/product-export/' . $productExport->getAccessKey() . '/' . $productExport->getFileName();

        $name = "DF – {$doofinderStoreFrontChannel->getName()} ({$domain->getLanguage()->getName()})";
        if (empty($trigger)) {
            $trigger = 'input[name="search"]';
        }
        $trigger = addslashes($trigger);

        $doofinderCommunication = $this->doofinderGraphCommunication->createStore($title, $trigger, $currency, $language, $domain->getUrl(), $searchEngineXml, $name);
        if (empty($doofinderCommunication['data']['create_store']['installation_id'])) {
            return $doofinderCommunication;
        }
        $storeId = $doofinderCommunication['data']['create_store']['installation_id'];
        $hashId = $doofinderCommunication['data']['create_store']['default_search_engine']['hashid'];

        $this->settingsHandler->createDoofinderEntity($hashId, $storeId, $domain->getId(), $name, $searchEngine);
        return $doofinderCommunication;
    }

    /**
     * Deletes Shopware DooFinder Entity
     *
     * @param $domainId
     * @return string|int|bool|array|object|float|null
     */
    public function deleteDoofinderExport($domainId)
    {
        $dooFinderLayerEntity = $this->settingsHandler->getDooFinderLayer($this->settingsHandler->getDomain($domainId));

        $doofinderEntity = [
            'id' => $dooFinderLayerEntity->getId()
        ];
        $this->settingsHandler->deleteDoofinderLayer($doofinderEntity, $this->settingsHandler->getContext());

        return $this->deleteSearchEngine(
            $dooFinderLayerEntity->getDooFinderHashId()
        );
    }

    /**
     * @param $domainId
     * @param $hashId
     * @return array|bool|float|int|object|string|string[]|null
     * @throws \Exception
     */
    public function getProcessDooFinderSearchIndex($domainId, $hashId)
    {
        $doofinderLayer = $this->settingsHandler->getDoofinderLayer($this->settingsHandler->getDomain($domainId));

        $date = new DateTimeImmutable($doofinderLayer->getStatusReceivedDate() ? : date('Y-m-d H:i:s'));

        if ($doofinderLayer->getStatusReceivedDate()) {
            $date = $date->modify("+1 Minutes");
        }

        $data = ['error' => 'To soon'];
        if ($date->format('Y-m-d H:i:s') <= date('Y-m-d H:i:s')) {
            $data = $this->management->getProcessDoofinderSearchIndex(
                $hashId
            );

            if (!$data) {
                $data = [
                    'status' => 'Error',
                    'result' => 'No SearchEngine found on Doofinder',
                    'finished_at' => '1970-01-01 00:00:00'
                ];
            }

            if (isset($data['result'])) {
                $this->settingsHandler->updateDoofinderLayer(
                    $doofinderLayer->getId(),
                    $data['status'],
                    $data['result'],
                    $data['finished_at']
                );
            } else {
                $this->settingsHandler->updateDoofinderLayer(
                    $doofinderLayer->getId(),
                    $data['status'],
                    $data['error_message'][0],
                    $data['finished_at']
                );
            }
        }
        return $data;
    }

    /**
     * @param $storeId
     * @return float|object|int|bool|array|string|null
     */
    public function deleteDoofinderStore($storeId)
    {
        $languages = $this->doofinderStore($storeId)[0]['config']['search_engines'];

        foreach ($languages as $currencies) {
            foreach($currencies as $searchEngines) {
                $doofinderLayer = $this->settingsHandler->getDoofinderLayerByHashId($searchEngines);

                if ($doofinderLayer) {
                    $this->settingsHandler->deleteSalesChannel($doofinderLayer);
                    $this->deleteDoofinderExport($doofinderLayer->getDomainId());
                    $doofinderEntity = [
                        'id' => $doofinderLayer->getId()
                    ];
                    $this->settingsHandler->deleteDoofinderLayer($doofinderEntity, $this->settingsHandler->getContext());
                }
            }
        }
        return $this->doofinderGraphCommunication->deleteStore($storeId);
    }

    /**
     * @param $hashId
     * @return array|bool|float|int|object|string|null
     */
    public function processDooFinderSearchIndex($hashId)
    {
        return $this->management->processDoofinderSearchIndex($hashId);
    }

    /**
     * @param string|null $getDooFinderHashId
     * @return array|bool|float|int|object|string|null
     */
    public function deleteSearchEngine(?string $getDooFinderHashId)
    {
        return $this->management->deleteSearchEngine($getDooFinderHashId);
    }

    /**
     * @param $storefrontChannelId
     * @param $domainId
     * @param $storeId
     * @param $hashId
     * @return string[]
     * @throws \Exception
     */
    public function createSalesChannelDoofinderExport(
        $storefrontChannelId,
        $domainId,
        $storeId,
        $hashId = null
    ) {
        $domain = $this->settingsHandler->getDomain($domainId);
        $storeData = $this->doofinderStore($storeId)[0];
        $storeDataCurrent = $storeData['config']['defaults'];

        $storeCurrency = $storeDataCurrent['currency'];
        $storeLanguage = $storeDataCurrent['language'];

        $languageCode = $domain->getLanguage()->getLocale()->getCode();
        $currencyCode = $domain->getCurrency()->getIsoCode();
        if (
            $storeCurrency == $currencyCode &&
            $storeLanguage == $languageCode &&
            $storeData['id'] == $storeId &&
            $storeDataCurrent['hashid'] != $hashId
        ) {
            return ['error' => 'languageAndCurrencyAlreadyInUse'];
        }
        if ($this->settingsHandler->getDoofinderLayerByHashId($hashId)) {
            return ['error' => 'searchEngineAlreadyInUse'];
        }

        $this->createDoofinderExportForDomainIfRequired(
            $this->settingsHandler->getStorefrontChannel($storefrontChannelId),
            $this->settingsHandler->getDomain($domainId),
            $storeId,
            $hashId
        );

        $this->editDoofinderStore($storeId, $domainId, null, null, $hashId);
    }

    /**
     * Creates Shopware Product Export for DooFinder
     *
     * @param SalesChannelEntity $storeFrontChannel
     * @param SalesChannelDomainEntity $domain
     * @param $storeId
     * @param $hashId
     * @return SalesChannelEntity|void
     * @throws \Exception
     */
    public function createDoofinderExportForDomainIfRequired(
        SalesChannelEntity $storeFrontChannel,
        SalesChannelDomainEntity $domain,
        $storeId = null,
        $hashId = null
    ) {
        $doofinderChannel = $this->settingsHandler->getDooFinderChannel($domain);

        if ($doofinderChannel) {
            if ($storeId) {
                $this->createDoofinderExport($storeFrontChannel, $domain, $storeId, $hashId);
            }

            $this->createDooFinderSearchIndex($doofinderChannel, $domain);
            $doofinderLayer = $this->settingsHandler->getDoofinderLayer($domain);

            if ($doofinderLayer) {
                $this->processDoofinderSearchIndex($doofinderLayer->getDooFinderHashId());
            }
            return $doofinderChannel;
        } elseif (is_null($doofinderChannel)) {
            if ($storeId != null) {
                $this->createDoofinderExport($storeFrontChannel, $domain, $storeId, $hashId);
            }

            $data = [
                'name'                          => "DF – {$storeFrontChannel->getName()} ({$domain->getLanguage()->getName()})",
                'typeId'                        => Defaults::SALES_CHANNEL_TYPE_PRODUCT_COMPARISON,
                'languageId'                    => $domain->getLanguage()->getId(),
                'customerGroupId'               => $storeFrontChannel->getCustomerGroupId(),
                'currencyId'                    => $storeFrontChannel->getCurrencyId(),
                'paymentMethodId'               => $storeFrontChannel->getPaymentMethodId(),
                'shippingMethodId'              => $storeFrontChannel->getShippingMethodId(),
                'countryId'                     => $storeFrontChannel->getCountryId(),
                'navigationCategoryId'          => $storeFrontChannel->getNavigationCategoryId(),
                'navigationCategoryIdVersionId' => Defaults::LIVE_VERSION,
                'accessKey'                     => $this->exportHandler->getSalesChannelAccessKey(),
                'productExports' => [
                    [
                        'productStreamId'          => $this->exportHandler->getDooFinderStream()->getId(),
                        'storefrontSalesChannelId' => $storeFrontChannel->getId(),
                        'interval'                 => 0,
                        'salesChannelDomainId'     => $domain->getId(),
                        'currencyId'               => $storeFrontChannel->getCurrencyId(),
                        'fileName'                 => "doofinder-{$domain->getId()}-{$domain->getLanguage()->getLocale()->getCode()}.xml",
                        'accessKey'                => $this->exportHandler->getSalesChannelAccessKey(),
                        'encoding'                 => ProductExportEntity::ENCODING_UTF8,
                        'fileFormat'               => ProductExportEntity::FILE_FORMAT_XML,
                        'headerTemplate'           => file_get_contents(__DIR__ . '/../../../../../resources/feed-header.xml'),
                        'bodyTemplate'             => file_get_contents(__DIR__ . '/../../../../../resources/feed-body.xml'),
                        'footerTemplate'           => file_get_contents(__DIR__ . '/../../../../../resources/feed-footer.xml'),
                        'includeVariants'          => true,
                        'generateByCronjob'        => false
                    ]
                ]
            ];

            $doofinderChannel = $this->settingsHandler->createDoofinderExport($data);

            if ($storeId != null) {
                $this->createDooFinderSearchIndex($doofinderChannel, $domain);
                $doofinderLayer = $this->settingsHandler->getDoofinderLayer($domain);

                $this->processDoofinderSearchIndex($doofinderLayer->getDooFinderHashId());
            }
            return $doofinderChannel;
        }
    }
}
