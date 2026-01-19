<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Content\DreiscSeoRedirect\Aggregate\DreiscSeoRedirectImportExportLog;

use Doctrine\DBAL\Connection;
use DreiscSeoPro\Core\Content\DreiscSeoRedirect\DreiscSeoRedirectDefinition;
use DreiscSeoPro\Core\Foundation\Dal\EntityRepository;
use Shopware\Core\Framework\Adapter\Cache\CacheClearer;
use Shopware\Core\Framework\DataAbstractionLayer\Cache\EntityCacheKeyGenerator;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Aggregation\Metric\CountAggregation;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;

/**
* @method DreiscSeoRedirectImportExportLogEntity    get(string $id, array $associations = null, ?Context $context = null, $disableCache = false)
* @method DreiscSeoRedirectImportExportLogSearchResult    search(Criteria $criteria, Context $context = null, $disableCache = false)
*/
class DreiscSeoRedirectImportExportLogRepository extends EntityRepository
{
    /**
     * @param \Shopware\Core\Framework\DataAbstractionLayer\EntityRepository $repository
     * @param Connection $connection
     * @param CacheClearer $cacheClearer
     * @param EntityCacheKeyGenerator $entityCacheKeyGenerator
     */
    public function __construct(\Shopware\Core\Framework\DataAbstractionLayer\EntityRepository $repository, private readonly Connection $connection, private readonly CacheClearer $cacheClearer, private readonly EntityCacheKeyGenerator $entityCacheKeyGenerator)
    {
        parent::__construct($repository);
    }

    public function truncate(): void
    {
        $idSearchResult = $this->searchIds(new Criteria());
        if (0 === $idSearchResult->getTotal()) {
            return;
        }

        foreach($idSearchResult->getIds() as $id) {
            $this->connection->executeUpdate(
                'DELETE FROM `dreisc_seo_redirect_import_export_log` WHERE `id` = :dreiscSeoRedirectId',
                ['dreiscSeoRedirectId' => Uuid::fromHexToBytes($id)]
            );
        }
    }

    public function getResult(): array
    {
        $total = $this->searchIds((new Criteria()))->getTotal();

        $errors = $this->searchIds(
            (new Criteria())
                ->addFilter(
                    new EqualsFilter('dreiscSeoRedirectId', null)
                )
        )->getTotal();

        return [
            'total' => $total,
            'errors' => $errors
        ];
    }

    public function getByRowIndex(int $rowIndex): ?DreiscSeoRedirectImportExportLogEntity
    {
        return $this->search((new Criteria())
            ->addFilter(
                new EqualsFilter('rowIndex', $rowIndex)
            )
            ->addAssociation('dreiscSeoRedirect')
        )->first();
    }
}

