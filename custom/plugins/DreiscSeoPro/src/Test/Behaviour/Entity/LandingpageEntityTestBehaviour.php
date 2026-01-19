<?php declare(strict_types=1);

namespace DreiscSeoPro\Test\Behaviour\Entity;

use DreiscSeoPro\Test\Behaviour\Entity\ProductEntityTestBehaviour\ProductEntityTestBehaviourStruct;
use Shopware\Core\Content\Landingpage\LandingpageEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\Test\TestDefaults;
use Symfony\Component\DependencyInjection\ContainerInterface;

trait LandingpageEntityTestBehaviour
{
    abstract protected static function getContainer(): ContainerInterface;
    abstract protected function _createDefaultSalesChannelContext(array $options = []);
    protected function _createLandingpage(\Closure $landingpageClosure = null, SalesChannelContext $salesChannelContext = null): ?LandingpageEntity
    {
        $landingpage = $this->getLandingpageBase($salesChannelContext);

        if ($landingpageClosure) {
            $landingpageClosure($landingpage);
        }

        $this->upsertLandingpage($landingpage);

        return $this->fetchLandingpage($landingpage['id']);
    }

    private function getLandingpageBase(?SalesChannelContext $salesChannelContext): array
    {
        if (null === $salesChannelContext) {
            $salesChannelContext = $this->_createDefaultSalesChannelContext();
        }

        return [
            'id' => Uuid::randomHex(),
            'name' => 'name_' . Uuid::randomHex(),
            'url' => 'url_' . Uuid::randomHex(),
            'salesChannels' => [
                ['id' => $salesChannelContext->getSalesChannelId()]
            ]
        ];
    }
    private function fetchLandingpage(string $id): ?LandingpageEntity
    {
        $criteria = new Criteria([$id]);
        $criteria->addAssociation('translations');

        return $this->getContainer()->get('landing_page.repository')
            ->search($criteria, Context::createCLIContext())
            ->first();
    }

    private function upsertLandingpage(array $product): void
    {
        $this->getContainer()->get('landing_page.repository')->upsert([$product], Context::createCLIContext());
    }
}
