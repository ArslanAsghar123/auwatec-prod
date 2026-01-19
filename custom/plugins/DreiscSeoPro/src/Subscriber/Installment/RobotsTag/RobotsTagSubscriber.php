<?php declare(strict_types=1);

namespace DreiscSeoPro\Subscriber\Installment\RobotsTag;

use DreiscSeoPro\Core\CustomSetting\CustomSettingLoader;
use DreiscSeoPro\Core\RobotsTag\RobotsTagFetcher;
use DreiscSeoPro\Core\RobotsTag\RobotsTagFetcherStruct;
use Shopware\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Content\LandingPage\LandingPageDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Storefront\Page\LandingPage\LandingPage;
use Shopware\Storefront\Page\LandingPage\LandingPageLoadedEvent;
use Shopware\Storefront\Page\Navigation\NavigationPage;
use Shopware\Storefront\Page\Navigation\NavigationPageLoadedEvent;
use Shopware\Storefront\Page\Page;
use Shopware\Storefront\Page\PageLoadedEvent;
use Shopware\Storefront\Page\Product\ProductPage;
use Shopware\Storefront\Page\Product\ProductPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RobotsTagSubscriber implements EventSubscriberInterface
{
    final public const DREISC_SEO_INSTALLMENT_ROBOTS_TAG_DATA = 'dreiscSeoInstallmentRobotsTagData';

    public function __construct(private readonly RobotsTagFetcher $robotsTagFetcher, private readonly CustomSettingLoader $customSettingLoader)
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
        $languageId = $event->getContext()->getLanguageId();
        $robotsTag = null;

        /** Load the custom settings */
        $customSettings = $this->customSettingLoader->load($salesChannelEntity->getId(), true);

        /** Fetch the params of the request */
        $requestParams = null;
        if (null !== $event->getRequest()->getQueryString()) {
            parse_str((string) $event->getRequest()->getQueryString(), $requestParams);
        }

        if ($page instanceof ProductPage) {
            $robotsTag = $this->robotsTagFetcher->fetch(
                new RobotsTagFetcherStruct(
                    $customSettings,
                    ProductDefinition::ENTITY_NAME,
                    $page->getProduct()->getId(),
                    $languageId,
                    $requestParams
                )
            );
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

            $robotsTag = $this->robotsTagFetcher->fetch(
                new RobotsTagFetcherStruct(
                    $customSettings,
                    CategoryDefinition::ENTITY_NAME,
                    $categoryId,
                    $languageId,
                    $requestParams
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

            $robotsTag = $this->robotsTagFetcher->fetch(
                new RobotsTagFetcherStruct(
                    $customSettings,
                    LandingPageDefinition::ENTITY_NAME,
                    $landingPageId,
                    $languageId,
                    $requestParams
                )
            );
        }

        if (null !== $robotsTag) {
            $page->addExtension(
                self::DREISC_SEO_INSTALLMENT_ROBOTS_TAG_DATA,
                new RobotsTagStruct($robotsTag)
            );
        }
    }
}
