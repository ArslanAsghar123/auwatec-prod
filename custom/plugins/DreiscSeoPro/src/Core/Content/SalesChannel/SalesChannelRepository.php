<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Content\SalesChannel;

use DreiscSeoPro\Core\Content\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainRepository;
use DreiscSeoPro\Core\Foundation\Dal\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;

/**
* @method SalesChannelEntity    get(string $id, array $associations = null, ?Context $context = null, $disableCache = false)
* @method SalesChannelSearchResult    search(Criteria $criteria, Context $context = null, $disableCache = false)
*/
class SalesChannelRepository extends EntityRepository
{
    /**
     * @var SalesChannelDomainRepository
     */
    private $salesChannelDomainRepository;

    /**
     * @var SalesChannelEntity[]
     */
    static public $cachedSalesChannelEntities = [];

    /**
     * EntityRepository constructor.
     * @param \Shopware\Core\Framework\DataAbstractionLayer\EntityRepository $repository
     * @param SalesChannelDomainRepository $salesChannelDomainRepository
     */
    public function __construct(\Shopware\Core\Framework\DataAbstractionLayer\EntityRepository $repository, SalesChannelDomainRepository $salesChannelDomainRepository)
    {
        $this->salesChannelDomainRepository = $salesChannelDomainRepository;

        parent::__construct($repository);
    }

    /**
     * @throws InconsistentCriteriaIdsException
     */
    public function getBySalesChannelDomainId(string $salesChannelDomainId): ?SalesChannelEntity
    {
        $salesChannelDomain = $this->salesChannelDomainRepository->get($salesChannelDomainId);

        if(null === $salesChannelDomain) {
            return null;
        }

        return $this->get($salesChannelDomain->getSalesChannelId());
    }

    public function getCached(string $salesChannelId): SalesChannelEntity
    {
        if (empty(self::$cachedSalesChannelEntities[$salesChannelId])) {
            self::$cachedSalesChannelEntities[$salesChannelId] = $this->get($salesChannelId);
        }

        return self::$cachedSalesChannelEntities[$salesChannelId];
    }
}

