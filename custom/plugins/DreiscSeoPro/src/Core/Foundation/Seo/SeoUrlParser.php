<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Foundation\Seo;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use DreiscSeoPro\Core\Foundation\Seo\Struct\SeoUrlParserResultStruct;
use DreiscSeoPro\Core\Foundation\String\StringAnalyzer;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainCollection;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainEntity;

class SeoUrlParser
{
    /**
     * @var EntityRepository
     */
    private $salesChannelDomainRepository;

    public function __construct(EntityRepository $salesChannelDomainRepository, private readonly StringAnalyzer $stringAnalyzer)
    {
        $this->salesChannelDomainRepository = $salesChannelDomainRepository;
    }

    /**
     * Parses an absolute url and gives you the following information:
     * - SalesChannelDomainEntity
     * - Base url
     *
     * @param $url
     */
    public function parse($url): ?SeoUrlParserResultStruct
    {
        $_stringAnalyzer = $this->stringAnalyzer;

        $salesChannelDomainCollection = $this->fetchSalesChannelDomainCollection();
        if (null === $salesChannelDomainCollection) {
            return null;
        }

        /** Filter the domain which not match to the given url */
        $filteredSalesChannelDomainCollection = $salesChannelDomainCollection->filter(static fn($salesChannelDomainEntity) => $_stringAnalyzer->stringStartsWith($url, $salesChannelDomainEntity->getUrl()));

        /** Abort, if to sales channel domain matches */
        if (0 === $filteredSalesChannelDomainCollection->count()) {
            return null;
        }

        /** Check for 1:1 match */
        $filteredOneToOneMatchSalesChannelDomainCollection = $filteredSalesChannelDomainCollection->filter(static fn($salesChannelDomainEntity) => $url === $salesChannelDomainEntity->getUrl());
        if(0 !== $filteredOneToOneMatchSalesChannelDomainCollection->count()) {
            $filteredSalesChannelDomainCollection = $filteredOneToOneMatchSalesChannelDomainCollection;
        }

        /**
         * Check, if we have more than one sales channel domains
         *
         * @example
         * URL: http://www.shopware-dev.de/en/this-is-my/base-url
         * Domain 1: http://www.shopware-dev.de/
         * Domain 2: http://www.shopware-dev.de/en/
         */
        if ($filteredSalesChannelDomainCollection->count() > 1) {
            $filteredSalesChannelDomainCollection = $filteredSalesChannelDomainCollection->filter(static function($salesChannelDomainEntity) use ($url, $_stringAnalyzer) {
                /** Make sure, that the domain ends with an slash */
                $domain = rtrim((string) $salesChannelDomainEntity->getUrl(), '/') . '/';

                return $_stringAnalyzer->stringStartsWith($url, $domain);
            });
        }

        /** Abort, if there is no sales channel domain */
        if (0 === $filteredSalesChannelDomainCollection->count()) {
            return null;
        }

        /**
         * If there are still more than one, we sort by length
         */
        if ($filteredSalesChannelDomainCollection->count() > 1) {
            $filteredSalesChannelDomainCollection->sort(
                static fn($salesChannelDomainEntityA, $salesChannelDomainEntityB) => mb_strlen((string) $salesChannelDomainEntityA->getUrl()) < mb_strlen((string) $salesChannelDomainEntityB->getUrl())
            );
        }

        /** We detected the sales channel domain entity */
        $seoUrlParserResultStruct = new SeoUrlParserResultStruct(
            $filteredSalesChannelDomainCollection->first()
        );

        /** In the next step we calculate the base url */
        $baseUrl = substr(
            (string) $url,
            mb_strlen((string) $seoUrlParserResultStruct->getSalesChannelDomainEntity()->getUrl())
        );

        /** Make sure that there is no slash at the beginning */
        $baseUrl = ltrim($baseUrl, '/');

        /** Put the base url into the struct */
        $seoUrlParserResultStruct->setBaseUrl($baseUrl);

        return $seoUrlParserResultStruct;
    }

    /**
     * @return SalesChannelDomainCollection[]|null
     */
    private function fetchSalesChannelDomainCollection(): ?SalesChannelDomainCollection
    {
        $entitySearchResult = $this->salesChannelDomainRepository->search(
            new Criteria(),
            Context::createDefaultContext()
        );

        if (0 === $entitySearchResult->getTotal()) {
            return null;
        }

        /** @var SalesChannelDomainCollection $salesChannelDomainCollection */
        $salesChannelDomainCollection = $entitySearchResult->getEntities();

        return $salesChannelDomainCollection;
    }
}
