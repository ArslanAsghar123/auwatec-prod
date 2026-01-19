<?php declare(strict_types=1);

namespace Intedia\Doofinder\Core\Content\Settings\Service;

use Intedia\Doofinder\Custom\DooFinderLayerEntity;
use Shopware\Core\Content\ProductStream\ProductStreamEntity;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\Currency\CurrencyEntity;
use Shopware\Core\System\Language\LanguageEntity;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainEntity;
use Shopware\Core\System\SalesChannel\SalesChannelDefinition;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;

/**
 * Class ExportHandler
 * @package Intedia\Doofinder\Core\Content\ProductExport\Service
 */
class SettingsHandler
{
    /** @var Context $context */
    private Context $context;

    /** @var EntityRepository|null $dooFinderLayerRepository */
    protected ?EntityRepository $dooFinderLayerRepository;

    /** @var EntityRepository $productExportRepository */
    protected EntityRepository $productExportRepository;

    /** @var EntityRepository $productStreamRepository */
    protected EntityRepository $productStreamRepository;

    /** @var EntityRepository $salesChannelRepository */
    protected $salesChannelRepository;

    /** @var EntityRepository $salesChannelDomainRepository */
    protected EntityRepository $salesChannelDomainRepository;

    /** @var EntityRepository $languageRepository */
    protected EntityRepository $languageRepository;

    /** @var EntityRepository $currencyRepository */
    protected EntityRepository $currencyRepository;

    public function __construct(
        EntityRepository $productExportRepository,
        EntityRepository $productStreamRepository,
        $salesChannelRepository,
        EntityRepository $salesChannelDomainRepository,
        EntityRepository $languageRepository,
        EntityRepository $currencyRepository,
        ?EntityRepository $dooFinderLayerRepository = null
    ) {
        $this->dooFinderLayerRepository     = $dooFinderLayerRepository;
        $this->productExportRepository      = $productExportRepository;
        $this->productStreamRepository      = $productStreamRepository;
        $this->salesChannelRepository       = $salesChannelRepository;
        $this->salesChannelDomainRepository = $salesChannelDomainRepository;
        $this->languageRepository           = $languageRepository;
        $this->currencyRepository           = $currencyRepository;
    }

    /**
     * @param $hashId
     * @param $storeId
     * @param $domainId
     * @param $name
     * @param $storeFrontChannel
     * @return void
     */
    public function createDoofinderEntity($hashId, $storeId, $domainId, $name, $storeFrontChannel): void
    {
        $doofinderEntity = [
            'id' => Uuid::randomHex(),
            'doofinderChannelId' => $storeFrontChannel->getId(),
            'doofinderHashId' => $hashId ?: '',
            'doofinderStoreId' => $storeId,
            'domainId' => $domainId,
            'name' => $name
        ];

        $this->dooFinderLayerRepository->create([$doofinderEntity], $this->context);
    }

    /**
     * @param $storefrontChannelId
     * @return SalesChannelEntity
     */
    public function getStorefrontChannel($storefrontChannelId): SalesChannelEntity
    {
        $salesChannelCriteria = new Criteria([$storefrontChannelId]);
        $salesChannelCriteria->addAssociation('domains')
            ->addAssociation('productExports');

        return $this->salesChannelRepository->search($salesChannelCriteria, $this->getContext())->first();
    }

    /**
     * @param $domainId
     * @return SalesChannelDomainEntity
     */
    public function getDomain($domainId): SalesChannelDomainEntity
    {
        $criteria = new Criteria([$domainId]);
        $criteria->addAssociation('language')
            ->addAssociation('currency')
            ->addAssociation('language.locale')
            ->addAssociation('domains.language.locale');

        return $this->salesChannelDomainRepository->search($criteria, $this->getContext())->first();
    }

    /**
     * @return EntityCollection
     */
    public function getStoreFrontChannels(): EntityCollection
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('type.id', Defaults::SALES_CHANNEL_TYPE_STOREFRONT))
            ->addAssociation('domains.language.locale')
            ->addAssociation('domains.productExports')
            ->addAssociation('currencies')
            ->addAssociation('domains.currency');

        return $this->salesChannelRepository->search($criteria, $this->getContext())->getEntities();
    }

    /**
     * @param SalesChannelDomainEntity|null $domain
     * @return SalesChannelEntity|null
     */
    public function getDooFinderChannel(?SalesChannelDomainEntity $domain): ?SalesChannelEntity
    {
        return $this->salesChannelRepository->search($this->getDooFinderFeedCriteria($domain), $this->getContext())->first();
    }

    /**
     * @param SalesChannelDomainEntity|null $domain
     * @return SalesChannelEntity|null
     */
    public function getSalesChannelByDomain(?SalesChannelDomainEntity $domain): ?SalesChannelEntity
    {
        $criteria = new Criteria();
        $criteria->addAssociation('domains');
        $criteria->addFilter(new EqualsFilter('domains.id', $domain->getId()));
        return $this->salesChannelRepository->search($criteria, $this->getContext())->first();
    }

    /**
     * @param SalesChannelDomainEntity|null $domain
     * @return DooFinderLayerEntity|null
     */
    public function getDoofinderLayer(?SalesChannelDomainEntity $domain): ?DooFinderLayerEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('domainId', $domain->getId()));
        return $this->dooFinderLayerRepository->search($criteria, $this->getContext())->first();
    }

    /**
     * @param $storeId
     * @return EntitySearchResult
     */
    public function getDoofinderLayersByStoreId($storeId): EntitySearchResult
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('doofinderStoreId', $storeId));
        return $this->dooFinderLayerRepository->search($criteria, $this->getContext());
    }

    /**
     * @param $hashId
     * @return DooFinderLayerEntity|null
     */
    public function getDoofinderLayerByHashId($hashId): ?DooFinderLayerEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('doofinderHashId', $hashId));
        return $this->dooFinderLayerRepository->search($criteria, $this->getContext())->first();
    }

    /**
     * @return array
     */
    public function getLanguages(): array
    {
        $criteria = new Criteria();
        $criteria->addAssociation('locale');

        $languagesCollection = $this->languageRepository->search($criteria, $this->getContext());
        $languages = [];
        /** @var LanguageEntity $language */
        foreach ($languagesCollection->getElements() as $language) {
            $languages[$language->getLocale()->getCode()] = $language->getName();
        }
        return $languages;
    }

    /**
     * @return array
     */
    public function getCurrencies(): array
    {
        $criteria = new Criteria();
        $criteria->addAssociation('currency.currency_translation');

        $currencies = [];
        $currencyCollection = $this->currencyRepository->search($criteria, $this->getContext());
        /** @var CurrencyEntity $currency */
        foreach ($currencyCollection->getElements() as $currency) {
            $currencies[$currency->getIsoCode()] = $currency->getName();
        }
        return $currencies;
    }

    /**
     * @return EntityCollection
     */
    public function getDooFinderChannels(): EntityCollection
    {
        return $this->salesChannelRepository->search($this->getDooFinderFeedCriteria(null), $this->getContext())->getEntities();
    }

    /**
     * @param $data
     * @return SalesChannelEntity
     */
    public function createDoofinderExport($data): SalesChannelEntity
    {
        $newDooFinderChannelIds = $this->salesChannelRepository->create([$data], $this->getContext());

        $salesChannelCriteria = new Criteria([$newDooFinderChannelIds->getPrimaryKeys(SalesChannelDefinition::ENTITY_NAME)[0]]);
        $salesChannelCriteria->addAssociation('domains')
            ->addAssociation('productExports')
            ->addAssociation('productExports.salesChannelDomain.language.locale')
            ->addAssociation('productExports.salesChannelDomain.currency')
            ->addAssociation('productExports.storefrontSalesChannel');
        return $this->salesChannelRepository->search($salesChannelCriteria, $this->getContext())->first();
    }

    /**
     * @throws InconsistentCriteriaIdsException
     */
    protected function getDooFinderFeedCriteria(?SalesChannelDomainEntity $domain): Criteria
    {
        $criteria = new Criteria();

        $criteria
            ->addAssociation('productExports.salesChannelDomain.language.locale')
            ->addAssociation('productExports.salesChannelDomain.currency')
            ->addAssociation('productExports.storefrontSalesChannel')
            ->addAssociation('domains.language.locale');
        $criteria->addFilter(new ContainsFilter('productExports.fileName', 'doofinder'));

        if ($domain) {
            $criteria->addFilter(new EqualsFilter('productExports.salesChannelDomainId', $domain->getId()));
        }

        return $criteria;
    }

    /**
     * @return Criteria
     */
    public function getDooFinderStreamCriteria(): Criteria
    {
        return (new Criteria())->addFilter(new EqualsFilter('name', 'DooFinder Produkte'));
    }

    /**
     * @param DooFinderLayerEntity $doofinderLayer
     * @return void
     */
    public function deleteSalesChannel(DooFinderLayerEntity $doofinderLayer): void
    {
        $salesChannel = $this->getDooFinderChannel($this->getDomain($doofinderLayer->getDomainId()));

        if (!empty($salesChannel)) {
            $this->salesChannelRepository->delete([[
                'id' => $salesChannel->getId(),
                'productExports' => null

            ]], $this->getContext());
        }
    }

    /**
     * Context Is created or used if its already there.
     * @return Context
     */
    public function getContext(): Context
    {
        $this->context = Context::createDefaultContext();
        return $this->context;
    }

    /**
     * @return bool
     */
    public function deleteDooFinderExports(): bool
    {
        foreach ($this->getDooFinderChannels() as $channel) {

            foreach ($channel->getProductExports() as $export) {
                $this->productExportRepository->delete([
                    [ 'id' => $export->getId() ]
                ], $this->getContext());
            }

            $this->salesChannelRepository->delete([
                [ 'id' => $channel->getId() ]
            ], $this->getContext());
        }

        return true;
    }

    /**
     * Function for the Uninstall Process of the Plugin
     * @return bool
     */
    public function deleteDooFinderStream(): bool
    {
        $entities = $this->productStreamRepository->search($this->getDooFinderStreamCriteria(), $this->getContext());

        /** @var ProductStreamEntity $stream */
        if ($stream = $entities->first()) {

            $this->productStreamRepository->delete([
                [ 'id' => $stream->getId() ]
            ], $this->getContext());
        }
        return true;
    }

    /**
     * Deletes DooFinder Entity from the Database
     *
     * @param $dooFinderEntity
     * @param Context $context
     * @return EntityWrittenContainerEvent
     */
    public function deleteDoofinderLayer($dooFinderEntity, Context $context): EntityWrittenContainerEvent
    {
        return $this->dooFinderLayerRepository->delete([$dooFinderEntity], $context);
    }

    /**
     * Updates DooFinder Layer with current Indexer Status
     *
     * @param $id
     * @param $status
     * @param $statusMessage
     * @param $statusDate
     * @return void
     */
    public function updateDoofinderLayer($id, $status, $statusMessage, $statusDate): void
    {
        $this->dooFinderLayerRepository->update([
            [
                'id' => $id,
                'status' => $status,
                'statusMessage' => $statusMessage,
                'statusDate' => date('Y-m-d H:i:s', strtotime($statusDate)),
                'statusReceivedDate' => date('Y-m-d H:i:s')
            ]
        ], $this->getContext());
    }
}
