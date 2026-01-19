<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Breadcrumb;

use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Content\Category\Service\CategoryBreadcrumbBuilder;
use Shopware\Core\Content\Category\Tree\TreeItem;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;

class PlainBreadcrumbCalculator
{
    public function __construct(private readonly CategoryBreadcrumbBuilder $categoryBreadcrumbBuilder)
    {
    }

    /**
     * @param TreeItem[] $navigationTree
     */
    public function getProductBreadcrumb(array $navigationTree, SalesChannelProductEntity $productEntity): ?array
    {
        $categoryTree = $productEntity->getCategoryTree();
        if(null === $categoryTree) {
            return null;
        }

        foreach($navigationTree as $item) {
            if ($item->getCategory()->getId() === end($categoryTree)) {
                $plainBreadcrumb = $item->getCategory()->getPlainBreadcrumb();

                if (empty($plainBreadcrumb)) {
                    return null;
                }

                /** Remove the shop root navigation element */
                return array_slice($plainBreadcrumb, 1);
            }

            if (count($item->getChildren()) > 0) {
                return $this->getProductBreadcrumb($item->getChildren(), $productEntity);
            }
        }

        return null;
    }

    public function getCategoryBreadcrumb(CategoryEntity $category, SalesChannelEntity $salesChannelEntity): ?array
    {
        return $this->categoryBreadcrumbBuilder->build(
            $category,
            $salesChannelEntity,
            $salesChannelEntity->getNavigationCategoryId()
        );
    }
}
