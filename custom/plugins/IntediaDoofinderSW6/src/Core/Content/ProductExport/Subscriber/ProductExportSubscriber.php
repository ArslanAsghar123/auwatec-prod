<?php declare(strict_types=1);

namespace Intedia\Doofinder\Core\Content\ProductExport\Subscriber;

use Doctrine\DBAL\Connection;
use Shopware\Core\Checkout\Customer\Aggregate\CustomerGroup\CustomerGroupEntity;
use Shopware\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Content\Category\Service\NavigationLoader;
use Shopware\Core\Content\Category\Tree\Tree;
use Shopware\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityDefinition;
use Shopware\Core\Content\Product\SalesChannel\ProductAvailableFilter;
use Shopware\Core\Content\ProductExport\Event\ProductExportProductCriteriaEvent;
use Shopware\Core\Content\ProductExport\Event\ProductExportRenderBodyContextEvent;
use Shopware\Core\Content\ProductExport\ProductExportEntity;
use Shopware\Core\Content\ProductStream\Service\ProductStreamBuilder;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProductExportSubscriber implements EventSubscriberInterface
{
    protected NavigationLoader $navigationLoader;

    protected ProductStreamBuilder $productStreamBuilder;

    protected EntityRepository $categoryRepository;

    protected Connection $connection;

    protected SalesChannelRepository $salesChannelRepository;

    protected EntityRepository $customerGroupRepository;

    protected SystemConfigService $systemConfigService;

    protected ?Tree $categoryTree = null;

    protected array $productStreamCategories = [];
    protected array $productNumberIdMapping = [];
    protected array $customerGroups = [];

    protected bool $usesDooFinderGroupIdExtension = false;
    protected bool $usesDooFinderCategoriesExtension = false;
    protected bool $usesDooFinderCustomerGroupPriceExtension = false;
    protected bool $usesDooFinderVariantInformationExtension = false;

    protected bool $runOnCriteriaEvent = false;

    public function __construct(
        NavigationLoader $navigationLoader,
        ProductStreamBuilder $productStreamBuilder,
        EntityRepository $categoryRepository,
        Connection $connection,
        SalesChannelRepository $salesChannelRepository,
        EntityRepository $customerGroupRepository,
        SystemConfigService $systemConfigService
    ) {
        $this->navigationLoader       = $navigationLoader;
        $this->productStreamBuilder   = $productStreamBuilder;
        $this->categoryRepository     = $categoryRepository;
        $this->connection             = $connection;
        $this->salesChannelRepository = $salesChannelRepository;
        $this->customerGroupRepository = $customerGroupRepository;
        $this->systemConfigService     = $systemConfigService;;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProductExportProductCriteriaEvent::class => 'onProductExportCriteriaEvent',
            ProductExportRenderBodyContextEvent::class => 'onProductExportRenderBodyContextEvent'
        ];
    }

    public function onProductExportCriteriaEvent(ProductExportProductCriteriaEvent $event): void
    {
        $criteria      = $event->getCriteria();

        $productExport = $event->getProductExport();
        $context       = $event->getSalesChannelContext();

        $this->usesDooFinderCategoriesExtension = $this->usesDooFinderCategoriesExtension($productExport);
        $this->usesDooFinderGroupIdExtension    = $this->usesDooFinderGroupIdExtension($productExport);
        $this->usesDooFinderCustomerGroupPriceExtension = $this->usesDooFinderCustomerGroupPriceExtension($productExport);
        $this->usesDooFinderVariantInformationExtension = $this->usesDooFinderVariantInformationExtension($productExport);

        if ($this->usesDooFinderVariantInformationExtension) {
            if (!$criteria->hasAssociation('children')) {
                $criteria->addAssociation('children');
            }
            if (!$criteria->hasAssociation('children.options')) {
                $criteria->addAssociation('children.options');
            }
            if (!$criteria->hasAssociation('children.options.group')) {
                $criteria->addAssociation('children.options.group');
            }
            if (!$criteria->hasAssociation('options')) {
                $criteria->addAssociation('options');
            }
            if (!$criteria->hasAssociation('options.group')) {
                $criteria->addAssociation('options.group');
            }
        }

        if ($this->usesDooFinderCategoriesExtension || $this->usesDooFinderGroupIdExtension || $this->usesDooFinderCustomerGroupPriceExtension) {

            $this->raiseMemoryLimit();

            if ($this->usesDooFinderCategoriesExtension) {
                $this->addDooFinderCategoriesData($criteria, $context);
            }

            if ($this->usesDooFinderGroupIdExtension) {
                $this->addDooFinderGroupIdData();
            }

            if ($this->usesDooFinderCustomerGroupPriceExtension) {
                $this->addDooFinderCustomerGroups($context);
            }
        }

        $this->runOnCriteriaEvent = true;
    }

    protected function addDooFinderCustomerGroups(SalesChannelContext $context)
    {
        $criteria = new Criteria();
        /** @var CustomerGroupEntity $group */
        $groups = $this->customerGroupRepository->search($criteria, $context->getContext());
        foreach ($groups as $group) {
            $this->customerGroups[$group->getName()] = $group->getId();
        }
    }

    protected function addDooFinderGroupIdData(): void
    {
        $this->productNumberIdMapping = $this->getProductNumberIdMapping();
    }

    protected function addDooFinderCategoriesData(Criteria $criteria, SalesChannelContext $context): void
    {
        if (!$criteria->hasAssociation('categories')) {
            $criteria->addAssociation('categories');
        }

        if ($context->getSalesChannel()) {

            $navigationCategoryId = $context->getSalesChannel()->getNavigationCategoryId();
            $this->categoryTree   = $this->navigationLoader->load($navigationCategoryId, $context, $navigationCategoryId, 10);
            $this->buildProductStreamCategories($context);
        }
    }

    protected function buildProductStreamCategories(SalesChannelContext $context)
    {
        foreach ($this->getCategoriesHavingStreams($context->getContext()) as $category)
        {
            foreach ($this->getProductIdsInStream($category, $context) as $productId)
            {
                if (!array_key_exists($productId, $this->productStreamCategories)) {
                    $this->productStreamCategories[$productId] = [];
                }

                $this->productStreamCategories[$productId][] = $category->getId();
            }
        }
    }

    public function onProductExportRenderBodyContextEvent(ProductExportRenderBodyContextEvent $event): void
    {
        $context = $event->getContext();

        if ($this->usesDooFinderVariantInformationExtension) {
            $context['DooFinderVariantInformation'] = true;
        }

        if ($this->runOnCriteriaEvent) {

            if ($this->usesDooFinderGroupIdExtension) {
                $context['groupIds'] = $this->productNumberIdMapping ?: [];
            }

            if ($this->usesDooFinderCategoriesExtension) {
                $context['categoryTree']            = $this->categoryTree ?: null;
                $context['productStreamCategories'] = $this->productStreamCategories ?: [];
            }

            if ($this->usesDooFinderCustomerGroupPriceExtension) {
                $context['customerGroups'] = $this->customerGroups ?: [];
            }
        }
        else {

            if (!array_key_exists('groupIds', $context)) {
                $context['groupIds'] = [];
            }

            if (!array_key_exists('categoryTree', $context)) {
                $context['categoryTree'] = null;
            }

            if (!array_key_exists('productStreamCategories', $context)) {
                $context['productStreamCategories'] = [];
            }

            if (!array_key_exists('customerGroups', $context)) {
                $context['customerGroups'] = [];
            }
        }


        $event->setContext($context);
    }

    protected function getProductIdsInStream(CategoryEntity $category, SalesChannelContext $salesChannelContext): array
    {
        if (is_null($category->getProductStreamId())) {
            return [];
        }

        try {

            $filters = $this->productStreamBuilder->buildFilters($category->getProductStreamId(), $salesChannelContext->getContext());

            $criteria = new Criteria();
            $criteria->addFilter(...$filters);

            $criteria->addFilter(
                new ProductAvailableFilter($salesChannelContext->getSalesChannel()->getId(), ProductVisibilityDefinition::VISIBILITY_ALL)
            );

            return $this->salesChannelRepository->searchIds($criteria, $salesChannelContext)->getIds();
        }
        catch (\Exception $exception) {
        }

        return [];
    }

    protected function getCategoriesHavingStreams(Context $context)
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('productAssignmentType', CategoryDefinition::PRODUCT_ASSIGNMENT_TYPE_PRODUCT_STREAM));

        return $this->categoryRepository->search($criteria, $context)->getEntities();
    }

    protected function usesDooFinderCategoriesExtension(ProductExportEntity $productExport): bool
    {
        return strpos($productExport->getBodyTemplate(), 'doofinderCategories') !== false;
    }

    protected function usesDooFinderVariantInformationExtension(ProductExportEntity $productExport): bool
    {
        return strpos($productExport->getBodyTemplate(), 'doofinderVariantInformation') !== false;
    }

    protected function usesDooFinderCustomerGroupPriceExtension(ProductExportEntity $productExport): bool
    {
        return strpos($productExport->getBodyTemplate(), 'doofinderCustomerGroupPrice') !== false;
    }

    protected function usesDooFinderGroupIdExtension(ProductExportEntity $productExport): bool
    {
        return strpos($productExport->getBodyTemplate(), 'doofinderGroupId') !== false;
    }

    protected function getProductNumberIdMapping(): array
    {
        return $this->connection->fetchAllKeyValue("SELECT lower(hex(id)), product_number from product");
    }

    protected function raiseMemoryLimit()
    {
        $configurationMemoryLimit = $this->getConfig('exportMemoryLimit');
        $currentMemoryLimit       = ini_get('memory_limit');

        if ($this->convertToBytes($configurationMemoryLimit) > $this->convertToBytes($currentMemoryLimit)) {
            ini_set('memory_limit', $configurationMemoryLimit);
        }
    }

    private function convertToBytes($value)
    {
        $value = trim($value);
        $last = strtolower($value[strlen($value) - 1]);
        $value = (int)$value;

        switch ($last) {
            case 'g':
                $value *= 1024 * 1024 * 1024;
                break;
            case 'm':
                $value *= 1024 * 1024;
                break;
            case 'k':
                $value *= 1024;
                break;
        }

        return $value;
    }

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
        }

        return null;
    }
}