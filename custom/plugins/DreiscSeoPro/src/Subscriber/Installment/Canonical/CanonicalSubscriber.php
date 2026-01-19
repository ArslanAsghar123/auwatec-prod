<?php declare(strict_types=1);

namespace DreiscSeoPro\Subscriber\Installment\Canonical;

use DreiscSeoPro\Core\Canonical\CanonicalFetcher;
use DreiscSeoPro\Core\Canonical\CanonicalFetcherStruct;
use DreiscSeoPro\Core\Content\Product\ProductEnum;
use DreiscSeoPro\Core\Seo\SeoDataFetcher\ProductSeoDataFetcher;
use Shopware\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Content\LandingPage\LandingPageDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\SalesChannelRequest;
use Shopware\Storefront\Page\LandingPage\LandingPage;
use Shopware\Storefront\Page\LandingPage\LandingPageLoadedEvent;
use Shopware\Storefront\Page\Navigation\NavigationPage;
use Shopware\Storefront\Page\Navigation\NavigationPageLoadedEvent;
use Shopware\Storefront\Page\Page;
use Shopware\Storefront\Page\PageLoadedEvent;
use Shopware\Storefront\Page\Product\ProductPage;
use Shopware\Storefront\Page\Product\ProductPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CanonicalSubscriber implements EventSubscriberInterface
{
    final public const DREISC_SEO_INSTALLMENT_CANONICAL_DATA = 'dreiscSeoInstallmentCanonicalData';

    public function __construct(private readonly CanonicalFetcher $canonicalFetcher)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProductPageLoadedEvent::class => 'addInstallment',
            NavigationPageLoadedEvent::class => 'addInstallment',
            LandingPageLoadedEvent::class => 'addInstallment',
        ];
    }

    /**
     * @throws InconsistentCriteriaIdsException
     */
    public function addInstallment(PageLoadedEvent $event)
    {
        /** @var Page|ProductPage|NavigationPage $page */
        $page = $event->getPage();
        $salesChannelEntity = $event->getSalesChannelContext()->getSalesChannel();
        $salesChannelDomainId = $event->getRequest()->attributes->get(SalesChannelRequest::ATTRIBUTE_DOMAIN_ID);
        $languageId = $event->getContext()->getLanguageId();

        if ($page instanceof ProductPage) {
            $canonicalLink = $this->canonicalFetcher->fetch(
                new CanonicalFetcherStruct(
                    ProductDefinition::ENTITY_NAME,
                    $page->getProduct()->getId(),
                    $page->getProduct(),
                    $languageId,
                    $salesChannelEntity->getId(),
                    $salesChannelDomainId
                )
            );

            if(!empty($canonicalLink)) {
                $page->getMetaInformation()->setCanonical($canonicalLink);
            }

        } elseif ($page instanceof NavigationPage) {

            /** Try to fetch the active category */
            $categoryId = $event->getRequest()->attributes->get('navigationId');

            /** Fetch the home category id */
            if(
                empty($categoryId) &&
                'frontend.home.page' === $event->getRequest()->attributes->get('_route') &&
                null !== $page->getHeader() &&
                null !== $page->getHeader()->getNavigation()
            ) {
                $activeCategory = $page->getHeader()->getNavigation()->getActive();
                if (null !== $activeCategory) {
                    $categoryId = $activeCategory->getId();
                }
            }

            /** Abort, if category is null */
            if (null === $categoryId) {
                return;
            }

            $canonicalLink = $this->canonicalFetcher->fetch(
                new CanonicalFetcherStruct(
                    CategoryDefinition::ENTITY_NAME,
                    $categoryId,
                    null,
                    $languageId,
                    $salesChannelEntity->getId(),
                    $salesChannelDomainId
                )
            );

        } elseif ($page instanceof LandingPage)
        {
            /** Try to fetch the landingpage id */
            $landingPageId = $event->getRequest()->attributes->get('landingPageId');

            /** Abort, if landingpage id is null */
            if (null === $landingPageId) {
                return;
            }

            $canonicalLink = $this->canonicalFetcher->fetch(
                new CanonicalFetcherStruct(
                    LandingPageDefinition::ENTITY_NAME,
                    $landingPageId,
                    null,
                    $languageId,
                    $salesChannelEntity->getId(),
                    $salesChannelDomainId
                )
            );
        }

        if (null !== $canonicalLink) {
            $page->addExtension(
                self::DREISC_SEO_INSTALLMENT_CANONICAL_DATA,
                new CanonicalDataStruct($canonicalLink)
            );
        }
    }
}
