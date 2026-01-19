<?php declare(strict_types=1);

namespace DreiscSeoPro\Subscriber\Installment\RichSnippet;

use DreiscSeoPro\Core\Breadcrumb\PlainBreadcrumbCalculator;
use DreiscSeoPro\Core\CustomSetting\CustomSettingLoader;
use DreiscSeoPro\Core\RichSnippet\Breadcrumb\BreadcrumbRichSnippetLdBuilderInterface;
use DreiscSeoPro\Core\RichSnippet\Breadcrumb\BreadcrumbRichSnippetLdBuilderStruct;
use DreiscSeoPro\Core\RichSnippet\LocalBusiness\LocalBusinessRichSnippetLdBuilderInterface;
use DreiscSeoPro\Core\RichSnippet\LocalBusiness\LocalBusinessRichSnippetLdBuilderStruct;
use DreiscSeoPro\Core\RichSnippet\Logo\LogoRichSnippetLdBuilderInterface;
use DreiscSeoPro\Core\RichSnippet\Logo\LogoRichSnippetLdBuilderStruct;
use DreiscSeoPro\Core\RichSnippet\Product\ProductRichSnippetLdBuilderInterface;
use DreiscSeoPro\Core\RichSnippet\Product\ProductRichSnippetLdBuilderStruct;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\SalesChannelRequest;
use Shopware\Storefront\Page\Navigation\NavigationPageLoadedEvent;
use Shopware\Storefront\Page\Page;
use Shopware\Storefront\Page\PageLoadedEvent;
use Shopware\Storefront\Page\Product\ProductPage;
use Shopware\Storefront\Page\Product\ProductPageLoadedEvent;
use Shopware\Storefront\Page\Product\Review\ProductReviewLoader;
use Shopware\Storefront\Page\Product\Review\ReviewLoaderResult;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RichSnippetSubscriber implements EventSubscriberInterface
{
    final public const DREISC_SEO_INSTALLMENT_RICH_SNIPPET_DATA = 'dreiscSeoInstallmentRichSnippetData';

    /**
     * @var ProductRichSnippetLdBuilderInterface
     */
    private $productRichSnippetLdBuilder;

    /**
     * @var BreadcrumbRichSnippetLdBuilderInterface
     */
    private $breadcrumbRichSnippetLdBuilder;

    /**
     * @var LogoRichSnippetLdBuilderInterface
     */
    private $logoRichSnippetLdBuilder;

    /**
     * @var LocalBusinessRichSnippetLdBuilderInterface
     */
    protected $localBusinessRichSnippetLdBuilder;

    /**
     * @var ProductReviewLoader
     */
    protected $productReviewLoader;

    /**
     * @param ProductRichSnippetLdBuilderInterface $productRichSnippetLdBuilder
     * @param BreadcrumbRichSnippetLdBuilderInterface $breadcrumbRichSnippetLdBuilder
     * @param LogoRichSnippetLdBuilderInterface $logoRichSnippetLdBuilder
     * @param LocalBusinessRichSnippetLdBuilderInterface $localBusinessRichSnippetLdBuilder
     */
    public function __construct(ProductRichSnippetLdBuilderInterface $productRichSnippetLdBuilder, BreadcrumbRichSnippetLdBuilderInterface $breadcrumbRichSnippetLdBuilder, LogoRichSnippetLdBuilderInterface $logoRichSnippetLdBuilder, private readonly PlainBreadcrumbCalculator $plainBreadcrumbCalculator, private readonly CustomSettingLoader $customSettingLoader, LocalBusinessRichSnippetLdBuilderInterface $localBusinessRichSnippetLdBuilder, ProductReviewLoader $productReviewLoader)
    {
        $this->productRichSnippetLdBuilder = $productRichSnippetLdBuilder;
        $this->breadcrumbRichSnippetLdBuilder = $breadcrumbRichSnippetLdBuilder;
        $this->logoRichSnippetLdBuilder = $logoRichSnippetLdBuilder;
        $this->localBusinessRichSnippetLdBuilder = $localBusinessRichSnippetLdBuilder;
        $this->productReviewLoader = $productReviewLoader;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProductPageLoadedEvent::class => 'addInstallment',
            NavigationPageLoadedEvent::class => 'addInstallment',
        ];
    }

    /**
     * @throws InconsistentCriteriaIdsException
     */
    public function addInstallment(PageLoadedEvent $event)
    {
        $ld = [];

        /** @var Page|ProductPage $page */
        $page = $event->getPage();
        /** @var SalesChannelProductEntity $productEntity */
        $productEntity = null;
        $salesChannelContext= $event->getSalesChannelContext();
        $salesChannelEntity = $salesChannelContext->getSalesChannel();
        $salesChannelDomainId = $event->getRequest()->attributes->get(SalesChannelRequest::ATTRIBUTE_DOMAIN_ID);
        $plainBreadcrumb = null;

        /** Load the custom settings */
        $customSettings = $this->customSettingLoader->load($salesChannelEntity->getId(), true);

        /** Abort, if json ld support was not activated */
        if (false === $customSettings->getRichSnippets()->getGeneral()->isActive()) {
            return;
        }

        /** Try to fetch the navigation tree and the active category */
        if (null !== $page->getHeader() && null !== $page->getHeader()->getNavigation()) {
            $navigationTree = $page->getHeader()->getNavigation()->getTree();
            $category = $page->getHeader()->getNavigation()->getActive();
        } else {
            $navigationTree = null;
            $category = null;
        }

        /** Product page */
        if ($page instanceof ProductPage) {
            /** Calculate product ld data */
            $productEntity = $page->getProduct();
            $currencyEntity = $event->getSalesChannelContext()->getCurrency();

            /** @todo: Shopware Bug > getReviews is null */
//            $reviewLoaderResult = $event->getPage()->getReviews();
            $reviewLoaderResult = $this->productReviewLoader->load(
                $event->getRequest(),
                $event->getSalesChannelContext()
            );

            /** Build the product ld data */
            $ld[] = $this->productRichSnippetLdBuilder->build(
                new ProductRichSnippetLdBuilderStruct(
                    $customSettings,
                    $productEntity,
                    $salesChannelEntity,
                    $currencyEntity,
                    $reviewLoaderResult,
                    $salesChannelContext,
                    $salesChannelDomainId
                )
            );

            /** Calculate breadcrumb ld data */
//            if (null !== $navigationTree) {
//                $plainBreadcrumb = $this->plainBreadcrumbCalculator->getProductBreadcrumb($navigationTree, $productEntity);
//            }
        } else {
            /** Calculate breadcrumb ld data */
//            if (null !== $navigationTree && null !== $category) {
//                $plainBreadcrumb = $this->plainBreadcrumbCalculator->getCategoryBreadcrumb($category, $salesChannelEntity);
//            }
        }

        if(null !== $category && null !== $salesChannelEntity) {
            $plainBreadcrumb = $this->plainBreadcrumbCalculator->getCategoryBreadcrumb($category, $salesChannelEntity);
        }

        /** Build the breadcrumb ld, if data available */
        if (null !== $plainBreadcrumb) {
            $ld[] = $this->breadcrumbRichSnippetLdBuilder->build(
                new BreadcrumbRichSnippetLdBuilderStruct(
                    $customSettings,
                    $plainBreadcrumb,
                    $salesChannelEntity,
                    $salesChannelDomainId,
                    $productEntity,
                    $salesChannelContext
                )
            );
        }

        /** Build the logo ld data */
        $ld[] = $this->logoRichSnippetLdBuilder->build(
            new LogoRichSnippetLdBuilderStruct(
                $customSettings,
                $salesChannelEntity
            )
        );

        /** Build the local business ld data */
        $ld[] = $this->localBusinessRichSnippetLdBuilder->build(
            new LocalBusinessRichSnippetLdBuilderStruct(
                $customSettings,
                $salesChannelEntity
            )
        );

        /** Move organization url to local business image */
        $ld = $this->copyOrganizationUrlToLocalBusinessImage($ld);

        if(!empty($ld)) {
            /** Wrap the ld data in a struct */
            $richSnippetDataStruct = new RichSnippetDataStruct($ld);

            if(!empty($richSnippetDataStruct->getLdJson())) {
                $page->addExtension(
                    self::DREISC_SEO_INSTALLMENT_RICH_SNIPPET_DATA,
                    $richSnippetDataStruct
                );
            }
        }
    }

    private function copyOrganizationUrlToLocalBusinessImage(array $ld)
    {
        $organizationUrl = null;
        foreach($ld as $ldItem) {
            if(empty($ldItem)) {
                continue;
            }

            if ('Organization' === $ldItem['@type'] && !empty($ldItem['url'])) {
                $organizationUrl = $ldItem['url'];
            }
        }

        if (null !== $organizationUrl) {
            foreach($ld as &$ldItem) {
                if(empty($ldItem)) {
                    continue;
                }

                if ('LocalBusiness' === $ldItem['@type']) {
                    $ldItem['image'] = $organizationUrl;
                }
            }
        }

        return $ld;
    }
}
