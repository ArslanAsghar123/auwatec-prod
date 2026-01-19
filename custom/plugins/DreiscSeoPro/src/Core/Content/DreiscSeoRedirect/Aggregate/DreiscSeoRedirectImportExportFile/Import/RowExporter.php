<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Content\DreiscSeoRedirect\Aggregate\DreiscSeoRedirectImportExportFile\Import;

use DreiscSeoPro\Core\Content\DreiscSeoRedirect\Aggregate\DreiscSeoRedirectImportExportFile\Struct\RowExportResultStruct;
use DreiscSeoPro\Core\Content\DreiscSeoRedirect\DreiscSeoRedirectCollection;
use DreiscSeoPro\Core\Content\DreiscSeoRedirect\DreiscSeoRedirectEntity;
use DreiscSeoPro\Core\Content\DreiscSeoRedirect\DreiscSeoRedirectEnum;
use DreiscSeoPro\Core\Content\DreiscSeoRedirect\DreiscSeoRedirectRepository;
use DreiscSeoPro\Core\Content\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainRepository;
use Shopware\Core\Content\ImportExport\Struct\Progress;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;

class RowExporter
{
    /**
     * @var SalesChannelDomainRepository
     */
    private $salesChannelDomainRepository;

    /**
     * @param SalesChannelDomainRepository $salesChannelDomainRepository
     */
    public function __construct(private readonly DreiscSeoRedirectRepository $dreiscSeoRedirectRepository, SalesChannelDomainRepository $salesChannelDomainRepository)
    {
        $this->salesChannelDomainRepository = $salesChannelDomainRepository;
    }

    /**
     * @return RowExportResultStruct[]
     */
    public function export(Progress $progress, int $offset, int $limit): array
    {
        $export = [];

        $dreiscSeoRedirectCollection = $this->fetchRedirects($progress, $offset, $limit);
        if (null === $dreiscSeoRedirectCollection) {
            return [];
        }

        /** @var DreiscSeoRedirectEntity $dreiscSeoRedirectEntity */
        foreach($dreiscSeoRedirectCollection as $dreiscSeoRedirectEntity) {
            $export[] = $this->createExportResult($dreiscSeoRedirectEntity);
        }

        return $export;
    }

    private function createExportResult(DreiscSeoRedirectEntity $dreiscSeoRedirectEntity): RowExportResultStruct
    {
        $rowExportResultStruct = new RowExportResultStruct();

        $rowExportResultStruct->setActive(true === $dreiscSeoRedirectEntity->getActive() ? '1' : '0');
        $rowExportResultStruct->setHttpStatusCode($dreiscSeoRedirectEntity->getRedirectHttpStatusCode() ?? '301');
        $rowExportResultStruct->setParameterForwarding(true === $dreiscSeoRedirectEntity->getParameterForwarding() ? '1' : '0');

        switch ($dreiscSeoRedirectEntity->getSourceType()) {
            case DreiscSeoRedirectEnum::SOURCE_TYPE__PRODUCT:
                if (null !== $dreiscSeoRedirectEntity->getSourceProduct()) {
                    $rowExportResultStruct->setSourceProductNumber(
                        $dreiscSeoRedirectEntity->getSourceProduct()->getProductNumber()
                    );
                }
                break;
            case DreiscSeoRedirectEnum::SOURCE_TYPE__CATEGORY:
                if (null !== $dreiscSeoRedirectEntity->getSourceCategory()) {
                    $rowExportResultStruct->setSourceCategoryId(
                        $dreiscSeoRedirectEntity->getSourceCategory()->getId()
                    );
                }
                break;
            case DreiscSeoRedirectEnum::SOURCE_TYPE__URL:
                if (null !== $dreiscSeoRedirectEntity->getSourceSalesChannelDomain()) {
                    $domain = trim((string) $dreiscSeoRedirectEntity->getSourceSalesChannelDomain()->getUrl(), '/');
                    $baseUrl = ltrim((string) $dreiscSeoRedirectEntity->getSourcePath(), '/');

                    $rowExportResultStruct->setSourceInternalUrl(sprintf(
                        '%s/%s',
                        $domain,
                        $baseUrl
                    ));
                }
                break;
            default:
                throw new \RuntimeException('Unknown source type: ' . $dreiscSeoRedirectEntity->getSourceType());
        }

        switch ($dreiscSeoRedirectEntity->getRedirectType()) {
            case DreiscSeoRedirectEnum::REDIRECT_TYPE__PRODUCT:
                if (null !== $dreiscSeoRedirectEntity->getRedirectProduct()) {
                    $rowExportResultStruct->setTargetProductNumber(
                        $dreiscSeoRedirectEntity->getRedirectProduct()->getProductNumber()
                    );
                }
                break;
            case DreiscSeoRedirectEnum::REDIRECT_TYPE__CATEGORY:
                if (null !== $dreiscSeoRedirectEntity->getRedirectCategory()) {
                    $rowExportResultStruct->setTargetCategoryId(
                        $dreiscSeoRedirectEntity->getRedirectCategory()->getId()
                    );
                }
                break;
            case DreiscSeoRedirectEnum::REDIRECT_TYPE__URL:
                if (null !== $dreiscSeoRedirectEntity->getRedirectSalesChannelDomain()) {
                    $domain = trim((string) $dreiscSeoRedirectEntity->getRedirectSalesChannelDomain()->getUrl(), '/');
                    $baseUrl = ltrim((string) $dreiscSeoRedirectEntity->getRedirectPath(), '/');

                    $rowExportResultStruct->setTargetInternalUrl(sprintf(
                        '%s/%s',
                        $domain,
                        $baseUrl
                    ));
                }
                break;
            case DreiscSeoRedirectEnum::REDIRECT_TYPE__EXTERNAL_URL:
                    $rowExportResultStruct->setTargetExternalUrl(
                        (string) $dreiscSeoRedirectEntity->getRedirectUrl()
                    );
                break;
            default:
                throw new \RuntimeException('Unknown source type: ' . $dreiscSeoRedirectEntity->getSourceType());
        }

        /** Deviating domain */
        if (
            $dreiscSeoRedirectEntity->getHasDeviatingRedirectSalesChannelDomain() &&
            null !== $dreiscSeoRedirectEntity->getDeviatingRedirectSalesChannelDomain()
        ) {
            $rowExportResultStruct->setTargetDeviatingDomain(
                $dreiscSeoRedirectEntity->getDeviatingRedirectSalesChannelDomain()->getUrl()
            );
        }

        /** Restriction domains */
        if (
            $dreiscSeoRedirectEntity->getHasSourceSalesChannelDomainRestriction()
        ) {
            $restrictionDomains = [];
            foreach($dreiscSeoRedirectEntity->getSourceSalesChannelDomainRestrictionIds() as $restrictionDomainId) {
                $salesChannelDomainEntity = $this->salesChannelDomainRepository->search(
                    (new Criteria())->addFilter(
                        new EqualsFilter('id', $restrictionDomainId)
                    )
                )->first();

                if (null === $salesChannelDomainEntity) {
                    continue;
                }

                $restrictionDomains[] = $salesChannelDomainEntity->getUrl();
            }

            if(!empty($restrictionDomains)) {
                $rowExportResultStruct->setSourceRestrictionDomains(
                    implode('|', $restrictionDomains)
                );
            }
        }

        return $rowExportResultStruct;
    }

    /**
     * @return DreiscSeoRedirectCollection|null
     */
    private function fetchRedirects(Progress $progress, int $offset, int $limit): ?DreiscSeoRedirectCollection
    {
        $entitySearchResult = $this->dreiscSeoRedirectRepository->search(
            (new Criteria())
                ->addAssociations([
                    'sourceProduct',
                    'sourceCategory',
                    'sourceSalesChannelDomain',
                    'redirectProduct',
                    'redirectCategory',
                    'redirectSalesChannelDomain',
                    'deviatingRedirectSalesChannelDomain'
                ])
                ->setTotalCountMode(Criteria::TOTAL_COUNT_MODE_EXACT)
                ->setOffset($offset)
                ->setLimit($limit)
                ->addSorting(
                    new FieldSorting('createdAt', FieldSorting::ASCENDING)
                )
        );

        /** We update the total value every time, as a redirect could be added during the export */
        $progress->setTotal($entitySearchResult->getTotal());

        /** Update the offset */
        $progress->setOffset($offset + $entitySearchResult->count());

        if (($offset + $limit) >= $entitySearchResult->getTotal()) {
            $progress->setState(Progress::STATE_SUCCEEDED);
        }

        return $entitySearchResult->getEntities();
    }
}
