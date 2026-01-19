<?php declare(strict_types=1);

namespace DreiscSeoPro\Test\Behaviour\Entity;

use DreiscSeoPro\Test\Behaviour\Entity\ProductEntityTestBehaviour\ProductEntityTestBehaviourStruct;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\Test\TestDefaults;
use Symfony\Component\DependencyInjection\ContainerInterface;

trait CategoryEntityTestBehaviour
{
    abstract protected static function getContainer(): ContainerInterface;
    abstract protected function _createDefaultSalesChannelContext(array $options = []);
    protected function _createCategory(\Closure $categoryClosure = null, SalesChannelContext $salesChannelContext = null): ?CategoryEntity
    {
        $category = $this->getCategoryBase($salesChannelContext);

        if ($categoryClosure) {
            $categoryClosure($category);
        }

        $this->upsertCategory($category);

        return $this->fetchCategory($category['id']);
    }

    private function getCategoryBase(?SalesChannelContext $salesChannelContext): array
    {
        if (null === $salesChannelContext) {
            $salesChannelContext = $this->_createDefaultSalesChannelContext();
        }

        return [
            'id' => Uuid::randomHex(),
            'parentId' => $salesChannelContext->getSalesChannel()->getNavigationCategoryId(),
            'name' => Uuid::randomHex()
        ];
    }
    private function fetchCategory(string $id): ?CategoryEntity
    {
        $criteria = new Criteria([$id]);
        $criteria->addAssociation('translations');

        return $this->getContainer()->get('category.repository')
            ->search($criteria, Context::createCLIContext())
            ->first();
    }

    private function upsertCategory(array $product): void
    {
        $this->getContainer()->get('category.repository')->upsert([$product], Context::createCLIContext());
    }
}
