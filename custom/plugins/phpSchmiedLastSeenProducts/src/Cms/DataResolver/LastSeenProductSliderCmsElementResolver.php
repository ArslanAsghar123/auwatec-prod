<?php

declare(strict_types=1);

namespace phpSchmied\LastSeenProducts\Cms\DataResolver;

use phpSchmied\LastSeenProducts\Service\LastSeenProductService;
use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Content\Cms\DataResolver\CriteriaCollection;
use Shopware\Core\Content\Cms\DataResolver\Element\AbstractCmsElementResolver;
use Shopware\Core\Content\Cms\DataResolver\Element\ElementDataCollection;
use Shopware\Core\Content\Cms\DataResolver\ResolverContext\ResolverContext;
use Shopware\Core\Content\Cms\SalesChannel\Struct\ProductSliderStruct;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Grouping\FieldGrouping;

class LastSeenProductSliderCmsElementResolver extends AbstractCmsElementResolver
{
    private const STATIC_SEARCH_KEY = 'last-seen-product-slider';
    private LastSeenProductService $lastSeenProductService;

    public function __construct(
        LastSeenProductService $lastSeenProductService
    )
    {
        $this->lastSeenProductService = $lastSeenProductService;
    }

    public function getType(): string
    {
        return 'last-seen-product-slider';
    }

    public function collect(CmsSlotEntity $slot, ResolverContext $resolverContext): ?CriteriaCollection
    {
        $config = $slot->getFieldConfig();
        if ($config->get('ajaxLoad') && $config->get('ajaxLoad')->getValue()) {
            return null;
        }

        $request = $resolverContext->getRequest();
        $seenProductIds = $this->lastSeenProductService->getLastSeenProductCollection($request->getSession(), $resolverContext->getSalesChannelContext(), '');
        if (!$seenProductIds->count()) {
            return null;
        }

        $collection = new CriteriaCollection();

        $criteria = new Criteria($seenProductIds->getIds());
        $criteria->addAssociation('cover');

        $criteria->addGroupField(new FieldGrouping('displayGroup'));
        $criteria->addFilter(
            new NotFilter(
                NotFilter::CONNECTION_AND,
                [new EqualsFilter('displayGroup', null)]
            )
        );

        $collection->add(self::STATIC_SEARCH_KEY . '_' . $slot->getUniqueIdentifier(), ProductDefinition::class, $criteria);

        return $collection->all() ? $collection : null;
    }

    public function enrich(CmsSlotEntity $slot, ResolverContext $resolverContext, ElementDataCollection $result): void
    {
        $slider = new ProductSliderStruct();
        $slot->setData($slider);

        $searchKey = self::STATIC_SEARCH_KEY . '_' . $slot->getUniqueIdentifier();
        $searchResult = $result->get($searchKey);
        if (!$searchResult) {
            return;
        }

        /** @var ProductCollection|null $products */
        $products = $searchResult->getEntities();
        if (!$products) {
            return;
        }

        $slider->setProducts($products);
    }
}
