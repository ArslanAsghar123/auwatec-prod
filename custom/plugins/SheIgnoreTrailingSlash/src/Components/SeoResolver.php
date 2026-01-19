<?php declare(strict_types=1);

namespace She\IgnoreTrailingSlash\Components;

use Doctrine\DBAL\Connection;
use She\IgnoreTrailingSlash\SheIgnoreTrailingSlash;
use Shopware\Core\Content\Seo\AbstractSeoResolver;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class SeoResolver extends AbstractSeoResolver
{
    /**
     * @var AbstractSeoResolver
     */
    private AbstractSeoResolver $innerService;

    
    /**
     * @var SystemConfigService
     */
    private SystemConfigService $systemConfigService;


    /**
     * @var Connection
     */
    private Connection $connection;


    public function __construct(AbstractSeoResolver $innerService, Connection $connection, SystemConfigService $systemConfigService)
    {
        $this->innerService = $innerService;
        $this->connection = $connection;
        $this->systemConfigService = $systemConfigService;
    }

    public function getDecorated(): AbstractSeoResolver
    {
        return $this->innerService;
    }

    public function resolve(string $languageId, string $salesChannelId, string $pathInfo): array
    {
        $result = $this->innerService->resolve($languageId, $salesChannelId, $pathInfo);

        if (ltrim($result['pathInfo'], '/') !== $pathInfo) {
            return $result;
        }

        $seoPathInfo = ltrim($pathInfo, '/');
        if ($seoPathInfo === '') {
            return ['pathInfo' => '/', 'isCanonical' => false];
        }

        $query = $this->connection->createQueryBuilder()
            ->select('seo_path_info as seoPathInfo', 'path_info pathInfo', 'is_canonical isCanonical')
            ->from('seo_url')
            ->where('language_id = :language_id')
            ->andWhere('(sales_channel_id = :sales_channel_id OR sales_channel_id IS NULL)');

        if($this->systemConfigService->get('SheIgnoreTrailingSlash.config.onlyIgnoreTrailingSlash')){
            $query->andWhere('seo_path_info LIKE CONCAT(TRIM(TRAILING "/" FROM :seoPath))');
        }else{
            $query->andWhere('seo_path_info LIKE CONCAT(TRIM(TRAILING "/" FROM :seoPath), "%")');
        }

        $query->addOrderBy('sales_channel_id IS NULL')
            ->addOrderBy('LENGTH(seoPathInfo)')
            ->addOrderBy('is_canonical', 'DESC')
            ->setMaxResults(1)
            ->setParameter('language_id', Uuid::fromHexToBytes($languageId))
            ->setParameter('sales_channel_id', Uuid::fromHexToBytes($salesChannelId))
            ->setParameter('seoPath', $seoPathInfo);

        $seoPath = $query->executeQuery()->fetchAssociative();

        $seoPath = $seoPath !== false
            ? $seoPath
            : ['pathInfo' => $seoPathInfo, 'seoPathInfo' => $seoPathInfo, 'isCanonical' => false];

        if (!$seoPath['isCanonical']) {
            $query = $this->connection->createQueryBuilder()
                ->select('path_info pathInfo', 'seo_path_info seoPathInfo')
                ->from('seo_url')
                ->where('language_id = :language_id')
                ->andWhere('sales_channel_id = :sales_channel_id')
                ->andWhere('id != :id')
                ->andWhere('path_info = :pathInfo')
                ->andWhere('is_canonical = 1')
                ->setMaxResults(1)
                ->setParameter('language_id', Uuid::fromHexToBytes($languageId))
                ->setParameter('sales_channel_id', Uuid::fromHexToBytes($salesChannelId))
                ->setParameter('id', $seoPath['id'] ?? '')
                ->setParameter('pathInfo', '/' . ltrim($seoPath['pathInfo'], '/'));

            $canonical = $query->executeQuery()->fetchAssociative();
            if ($canonical) {
                $seoPath['canonicalPathInfo'] = '/' . ltrim($canonical['seoPathInfo'], '/');
            }
        }

        $seoPath['pathInfo'] = '/' . ltrim($seoPath['pathInfo'], '/');

        return $seoPath;
    }
}
