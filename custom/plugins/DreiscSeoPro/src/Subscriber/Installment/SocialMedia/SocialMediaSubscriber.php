<?php declare(strict_types=1);

namespace DreiscSeoPro\Subscriber\Installment\SocialMedia;

use DreiscSeoPro\Core\CustomSetting\CustomSettingLoader;
use DreiscSeoPro\Core\Seo\LiveTemplate\LiveTemplateConverter;
use DreiscSeoPro\Core\SocialMedia\SocialMediaFetcher;
use DreiscSeoPro\Core\SocialMedia\SocialMediaFetcherStruct;
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

class SocialMediaSubscriber implements EventSubscriberInterface
{
    final public const DREISC_SEO_INSTALLMENT_SOCIAL_MEDIA_DATA = 'dreiscSeoInstallmentSocialMediaData';

    public function __construct(
        private readonly CustomSettingLoader $customSettingLoader,
        private readonly SocialMediaFetcher $socialMediaFetcher
    ) { }

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
        $seoDataFetchResultStruct = null;

        /** Load the custom settings */
        $customSettings = $this->customSettingLoader->load($salesChannelEntity->getId(), true);

        if ($page instanceof ProductPage) {
            $seoDataFetchResultStruct = $this->socialMediaFetcher->fetch(
                new SocialMediaFetcherStruct(
                    $customSettings,
                    ProductDefinition::ENTITY_NAME,
                    $page->getProduct()->getId(),
                    $languageId,
                    $event->getSalesChannelContext(),
                    $page->getProduct()
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

            $seoDataFetchResultStruct = $this->socialMediaFetcher->fetch(
                new SocialMediaFetcherStruct(
                    $customSettings,
                    CategoryDefinition::ENTITY_NAME,
                    $categoryId,
                    $languageId,
                    $event->getSalesChannelContext()
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

            $seoDataFetchResultStruct = $this->socialMediaFetcher->fetch(
                new SocialMediaFetcherStruct(
                    $customSettings,
                    LandingPageDefinition::ENTITY_NAME,
                    $landingPageId,
                    $languageId,
                    $event->getSalesChannelContext()
                )
            );
        }

        if (null !== $seoDataFetchResultStruct) {
            $page->addExtension(
                self::DREISC_SEO_INSTALLMENT_SOCIAL_MEDIA_DATA,
                new SocialMediaDataStruct(
                    $seoDataFetchResultStruct->getFacebookTitle(),
                    $seoDataFetchResultStruct->getFacebookDescription(),
                    $seoDataFetchResultStruct->getFacebookImage(),
                    $seoDataFetchResultStruct->getTwitterTitle(),
                    $seoDataFetchResultStruct->getTwitterDescription(),
                    $seoDataFetchResultStruct->getTwitterImage()
                )
            );
        }
    }
}
