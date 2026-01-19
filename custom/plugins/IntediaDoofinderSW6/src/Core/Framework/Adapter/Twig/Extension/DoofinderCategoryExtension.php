<?php declare(strict_types=1);

namespace Intedia\Doofinder\Core\Framework\Adapter\Twig\Extension;

use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Content\Category\Service\CategoryBreadcrumbBuilder;
use Shopware\Core\Content\Category\Tree\Tree;
use Shopware\Core\Content\Product\ProductEntity;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class DoofinderCategoryExtension extends AbstractExtension
{
    private CategoryBreadcrumbBuilder $categoryBreadcrumbBuilder;
    private SystemConfigService $config;

    public function __construct(CategoryBreadcrumbBuilder $categoryBreadcrumbBuilder, SystemConfigService $config)
    {
        $this->categoryBreadcrumbBuilder = $categoryBreadcrumbBuilder;
        $this->config = $config;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('doofinderCategories', [$this, 'doofinderCategories']),
        ];
    }

    public function doofinderCategories(ProductEntity $product, ?array $productStreamCategories, ?Tree $categoryTree)
    {
        $doofinderCategories = [];
        if ($product->getCategories()) {
            foreach ($product->getCategories() as $category) {
                if ($category->getActive() &&
                    $this->isActiveSalesChannelCategory($categoryTree, $category) &&
                    $category->getParentId()
                ) {
                    $doofinderCategories[] = join(' > ', $this->getFilteredBreadcrumb($category, $categoryTree));
                }
            }

            $streamCategories = [];

            if (is_array($productStreamCategories) && array_key_exists($product->getId(), $productStreamCategories)) {
                $streamCategories = $productStreamCategories[$product->getId()];
            }

            if (is_array($productStreamCategories) && array_key_exists($product->getParentId(), $productStreamCategories)) {
                $streamCategories = $productStreamCategories[$product->getParentId()];
            }

            foreach ($streamCategories as $productStreamCategoryId) {

                $category = $this->getCategoryById($productStreamCategoryId, $categoryTree);

                if ($category && $category->getActive() && $this->isActiveSalesChannelCategory($categoryTree, $category) && $category->getParentId()) {
                    $doofinderCategories[] = join(' > ', $this->getFilteredBreadcrumb($category, $categoryTree));
                }
            }
        }

        return $doofinderCategories;
    }

    protected function getCategoryById(string $categoryId, ?Tree $categoryTree): ?CategoryEntity
    {
        if (is_null($categoryTree)) {
            return null; // old shopware version, which does is not supported for tree calculation
        }
        $childTree = $categoryTree->getChildren($categoryId);
        return $childTree ? $childTree->getActive() : null;
    }

    /**
     * @param Tree|null $categoryTree
     * @param CategoryEntity $category
     * @return bool
     */
    protected function isActiveSalesChannelCategory(?Tree $categoryTree, CategoryEntity $category): bool
    {
        if (is_null($categoryTree)) {
            return true; // old shopware version, which does is not supported for tree calculation -> handle as active sales channel category
        }
        return $categoryTree->getChildren($category->getId()) !== null;
    }

    /**
     * @param $category
     * @param Tree|null $categoryTree
     * @return array
     */
    protected function getFilteredBreadcrumb($category, ?Tree $categoryTree): array
    {
        $breadcrumb = $this->categoryBreadcrumbBuilder->build($category, null,
            $categoryTree ? $categoryTree->getActive()->getId() : $category->getParentId());
        if($this->config->get('IntediaDoofinderSW6.config.doofinderFilterBreadcrumb')) {
            return array_filter($breadcrumb, function ($key) use ($categoryTree) {
                return $this->isTypePage($key, $categoryTree);
            }, ARRAY_FILTER_USE_KEY);
        } else {
            return $breadcrumb;
        }
    }

    /**
     * @param string $categoryId
     * @param Tree|null $categoryTree
     * @return bool
     */
    protected function isTypePage(string $categoryId, ?Tree $categoryTree): bool
    {
        $category = $this->getCategoryById($categoryId, $categoryTree);
        return !$category || $category->getType() == 'page'; // old shopware version, which does is not supported for tree calculation -> handle as type page
    }
}
