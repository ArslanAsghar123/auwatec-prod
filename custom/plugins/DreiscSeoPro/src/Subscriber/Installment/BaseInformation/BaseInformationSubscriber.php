<?php declare(strict_types=1);

namespace DreiscSeoPro\Subscriber\Installment\BaseInformation;

use DreiscSeoPro\Core\Seo\LiveTemplate\LiveTemplateConverter;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Storefront\Page\LandingPage\LandingPageLoadedEvent;
use Shopware\Storefront\Page\Navigation\NavigationPageLoadedEvent;
use Shopware\Storefront\Page\Page;
use Shopware\Storefront\Page\PageLoadedEvent;
use Shopware\Storefront\Page\Product\ProductPage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BaseInformationSubscriber implements EventSubscriberInterface
{
    final public const DREISC_SEO_INSTALLMENT_BASE_INFORMATION_DATA = 'dreiscSeoInstallmentBaseInformationData';

    public function __construct(
        private readonly LiveTemplateConverter $liveTemplateConverter
    ) { }

    public static function getSubscribedEvents(): array
    {
        return [
            NavigationPageLoadedEvent::class => 'addInstallment',
        ];
    }

    /**
     * @throws InconsistentCriteriaIdsException
     */
    public function addInstallment(PageLoadedEvent $event)
    {
        /** @var Page|ProductPage $page */
        $page = $event->getPage();

        $this->liveTemplateConverter->translateMetaInformation($page->getMetaInformation(), $event->getSalesChannelContext());

        $baseInformationDataStruct = new BaseInformationDataStruct(
            $event->getRequest()->attributes->get('navigationId')
        );

        $page->addExtension(
            self::DREISC_SEO_INSTALLMENT_BASE_INFORMATION_DATA,
            $baseInformationDataStruct
        );
    }
}
