<?php declare(strict_types=1);

namespace Intedia\Doofinder\Administration\Controller;

use Intedia\Doofinder\Core\Content\ProductExport\Service\ExportHandler;
use Intedia\Doofinder\Core\Content\Settings\Service\CommunicationHandler;
use Intedia\Doofinder\Core\Content\Settings\Service\SettingsHandler;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainEntity;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: ['_routeScope' => ['api']])]
class ManagementController extends AbstractController
{
    protected ExportHandler $exportHandler;
    protected SystemConfigService $systemConfigService;
    protected SettingsHandler $settingsHandler;
    protected CommunicationHandler $communicationHandler;
    protected string $logDir;

    public function __construct(
        ExportHandler $exportHandler,
        SystemConfigService $systemConfigService,
        SettingsHandler $settingsHandler,
        CommunicationHandler $communicationHandler,
        string $logDir
    ) {
        $this->exportHandler        = $exportHandler;
        $this->systemConfigService  = $systemConfigService;
        $this->settingsHandler      = $settingsHandler;
        $this->communicationHandler = $communicationHandler;
        $this->logDir               = rtrim($logDir, '/') . '/';
    }

    #[Route(path: '/api/getDoofinderChannels', name: 'api.intedia.doofinder.getChannels', methods: ['GET'])]
    public function getDoofinderChannels(Request $request, Context $context): JsonResponse
    {
        $stores = $this->communicationHandler->doofinderStores();

        foreach ($stores as $key => $store) {
            $stores[$store['id']] = $store;
            unset($stores[$key]);
        }
        $data = [];
        foreach ($this->settingsHandler->getStoreFrontChannels() as $storeFrontChannel) {
            /** @var SalesChannelDomainEntity $domain */
            foreach ($storeFrontChannel->getDomains() as $domain) {
                $doofinderChannel = $this->settingsHandler->getDooFinderChannel($domain);
                $doofinderChannelId = '';
                if ($doofinderChannel) {
                    $doofinderChannelId = $doofinderChannel->getId();
                }
                $doofinderLayer = $this->settingsHandler->getDooFinderLayer($domain);
                $doofinderHashId = ''; $doofinderStoreId = ''; $doofinderId = ''; $storeName = ''; $doofinderSearchEngine = '';
                $doofinderStatus = false;
                $doofinderStatusMessage = null;
                if ($doofinderLayer) {
                    $doofinderId = $doofinderLayer->getId();
                    $doofinderHashId = $doofinderLayer->getDooFinderHashId();
                    $doofinderStatus = $doofinderLayer->getStatus() === 'SUCCESS';
                    $doofinderStatusMessage = str_replace('\r', '', $doofinderLayer->getStatusMessage() ? : '');
                    $doofinderStoreId = $doofinderLayer->getDoofinderStoreId();

                    if (isset($stores[$doofinderStoreId])) {
                        foreach ($stores[$doofinderStoreId]['searchEngines'] as $searchEngine) {
                            if ($searchEngine['id'] == $doofinderHashId) {
                                $doofinderSearchEngine = $searchEngine['name'];
                            }
                        }
                    }
                }
                if ($doofinderStoreId && isset($stores[$doofinderStoreId])) {
                    $storeName = $stores[$doofinderStoreId]['name'];
                }

                $data[] = [
                    'id' => $domain->getId(),
                    'storefront_channel_id' => $storeFrontChannel->getId(),
                    'doofinder_id' => $doofinderId,
                    'doofinder_channel_id' => $doofinderChannelId,
                    'doofinder_hash_id' => $doofinderHashId,
                    'doofinder_store_id' => $doofinderStoreId,
                    'doofinder_store_name' => $storeName,
                    'doofinder_status' => $doofinderStatus,
                    'doofinder_status_message' => $doofinderStatusMessage,
                    'domain_id' => $domain->getId(),
                    'domain' => $domain->getUrl(),
                    'storefront' => $storeFrontChannel->getName(),
                    'language' => $domain->getLanguage()->getName(),
                    'currency' => $domain->getCurrency()->getIsoCode(),
                    'searchengine' => (bool)$doofinderHashId,
                    'stores' => $stores,
                    'doofinderSearchEngine' => $doofinderSearchEngine
                ];
            }
        }

        return new JsonResponse($data);
    }

    #[Route(path: '/api/createDoofinderSearchEngine', name: 'api.intedia.doofinder.createSearchEngine', methods: ['POST'])]
    public function createDoofinderSearchEngine(Request $request, Context $context): JsonResponse
    {
        $data = $this->communicationHandler->createSalesChannelDoofinderExport(
            $request->get('storefrontChannelId'),
            $request->get('domainId'),
            $request->get('doofinderStoreId')
        );

        return new JsonResponse($data);
    }

    #[Route(path: '/api/linkDoofinderSearchEngine', name: 'api.intedia.doofinder.linkSearchEngine', methods: ['POST'])]
    public function linkDoofinderSearchEngine(Request $request, Context $context): JsonResponse
    {
        $data = $this->communicationHandler->createSalesChannelDoofinderExport(
            $request->get('storefrontChannelId'),
            $request->get('domainId'),
            $request->get('doofinderStoreId'),
            $request->get('hashId')
        );

        return new JsonResponse($data);
    }

    #[Route(path: '/api/processDoofinderSearchIndex', name: 'api.intedia.doofinder.createSearchIndex', methods: ['POST'])]
    public function processDoofinderSearchIndex(Request $request, Context $context): JsonResponse
    {
        return new JsonResponse(
            $this->communicationHandler->processDooFinderSearchIndex(
                $request->get('doofinderHashId')
            )
        );
    }

    #[Route(path: '/api/getProcessDoofinderSearchIndex', name: 'api.intedia.doofinder.getSearchIndex', methods: ['GET'])]
    public function getProcessDoofinderSearchIndex(Request $request, Context $context): JsonResponse
    {
        return new JsonResponse(
            $this->communicationHandler->getProcessDooFinderSearchIndex(
                $request->get('domainId'),
                $request->get('doofinderHashId')
            )
        );
    }

    #[Route(path: '/api/deleteDoofinderSearchEngine', name: 'api.intedia.doofinder.deleteSearchEngine', methods: ['POST'])]
    public function deleteSearchEngineAndSalesChannel(Request $request, Context $context): JsonResponse
    {
        return new JsonResponse(
            $this->communicationHandler->deleteDoofinderExport(
                $request->get('domainId')
            )
        );
    }

    #[Route(path: '/api/getStores', name: 'api.intedia.doofinder.store.getStores', methods: ['GET'])]
    public function getDoofinderStores(Request $request, Context $context): JsonResponse
    {
        return new JsonResponse(
            $this->communicationHandler->doofinderStores()
        );
    }

    #[Route(path: '/api/getLanguages', name: 'api.intedia.doofinder.store.getLanguages', methods: ['GET'])]
    public function getLanguages(Request $request, Context $context): JsonResponse
    {
        return new JsonResponse(
            $this->settingsHandler->getLanguages()
        );
    }

    #[Route(path: '/api/getCurrencies', name: 'api.intedia.doofinder.store.getCurrencies', methods: ['GET'])]
    public function getCurrencies(Request $request, Context $context): JsonResponse
    {
        return new JsonResponse(
            $this->settingsHandler->getCurrencies()
        );
    }

    #[Route(path: '/api/getStore', name: 'api.intedia.doofinder.store.getStore', methods: ['GET'])]
    public function getDoofinderStore(Request $request, Context $context): JsonResponse
    {
        return new JsonResponse(
            $this->communicationHandler->doofinderStore(
                $request->get('id')
            )
        );
    }

    #[Route(path: '/api/editStore', name: 'api.intedia.doofinder.store.editStore', methods: ['POST'])]
    public function editDoofinderStore(Request $request, Context $context): JsonResponse
    {
        return new JsonResponse(
            $this->communicationHandler->editDoofinderStore(
                $request->get('id'),
                $request->get('intediaDoofinder_domain_id'),
                $request->get('intediaDoofinder_title'),
                $request->get('intediaDoofinder_trigger')
            )
        );
    }

    #[Route(path: '/api/deleteStore', name: 'api.intedia.doofinder.store.deleteStore', methods: ['POST'])]
    public function deleteDoofinderStore(Request $request, Context $context): JsonResponse
    {
        return new JsonResponse(
            $this->communicationHandler->deleteDoofinderStore(
                $request->get('id')
            )
        );
    }

    #[Route(path: '/api/deleteLink', name: 'api.intedia.doofinder.searchEngine.deleteLink', methods: ['POST'])]
    public function deleteSearchEngineLink(Request $request, Context $context): JsonResponse
    {
        $doofinderEntity = [
            'id' => $request->get('id')
        ];

        return new JsonResponse(
            $this->settingsHandler->deleteDoofinderLayer(
                $doofinderEntity,
                $this->settingsHandler->getContext()
            )
        );
    }

    #[Route(path: '/api/createStore', name: 'api.intedia.doofinder.store.createStore', methods: ['POST'])]
    public function createDoofinderStore(Request $request, Context $context): JsonResponse
    {
        return new JsonResponse(
            $this->communicationHandler->createDoofinderStore(
                $request->get('intediaDoofinder_domain_id'),
                $request->get('intediaDoofinder_title'),
                $request->get('intediaDoofinder_trigger')
            )
        );
    }

    #[Route(path: '/api/doofinderLastLog', name: 'api.intedia.doofinder.lastLog', methods: ['GET'])]
    public function lastLog(Request $request, Context $context): Response
    {
        $lines = [];

        if ($this->getConfig('doofinderDebug')) {

            $file = fopen($this->logDir . "/intedia_doofinder_prod-" . date('Y-m-d') . ".log", "r");

            if (!$file) {
                $file = fopen($this->logDir . "/dev.log", "r");
            }

            if ($file) {
                while(!feof($file)) {
                    $lines[] = fgets($file);
                }
                fclose($file);
            }
        }

        return new JsonResponse($lines);
    }

    /**
     * @param $configKey
     * @param null $context
     * @return mixed|null
     */
    protected function getConfig($configKey, $context = null)
    {
        try {

            $prefix       = 'IntediaDoofinderSW6.config.';
            $pluginConfig = $this->systemConfigService->getDomain($prefix, $context ? $context->getSalesChannel()->getId() : null, true);
            $configKey    = $prefix . $configKey;

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
