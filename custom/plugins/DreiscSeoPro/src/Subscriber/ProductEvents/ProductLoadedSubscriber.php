<?php declare(strict_types=1);

namespace DreiscSeoPro\Subscriber\ProductEvents;

use DreiscSeoPro\Core\Seo\LiveTemplate\LiveTemplateConverter;
use PHPUnit\Exception;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Content\Category\CategoryEvents;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\Product\ProductEvents;
use Shopware\Core\Framework\Api\Context\SalesChannelApiSource;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProductLoadedSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly LiveTemplateConverter $liveTemplateConverter)
    {
    }

    /**
     * @return array|void
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'sales_channel.' . ProductEvents::PRODUCT_LOADED_EVENT => 'onProductLoaded'
        ];
    }

    public function onProductLoaded(EntityLoadedEvent $entityLoadedEvent): void
    {
        /** @var ProductEntity $productEntity */
        $productEntity = current($entityLoadedEvent->getEntities());

        /** Abort, if empty */
        if (false === $productEntity) {
            return;
        }

        $this->provideCustomFields($productEntity);

        /** At this point the seo live template will be converted */
        $this->liveTemplateConverter->translateProductEntity($productEntity, $entityLoadedEvent->getContext());
    }

    private function provideCustomFields(ProductEntity $productEntity)
    {
        $customFields = $productEntity->getCustomFields();
        if (null === $customFields) {
            $customFields = [];
        }

        $customFieldsKeys = [
            'dreisc_seo_rich_snippet_item_condition' => null,
            'dreisc_seo_rich_snippet_availability' => null,
            'dreisc_seo_rich_snippet_custom_sku' => null,
            'dreisc_seo_rich_snippet_custom_mpn' => null,
            'dreisc_seo_rich_snippet_price_valid_until_date' => null,
            'dreisc_seo_facebook_title' => null,
            'dreisc_seo_facebook_description' => null,
            'dreisc_seo_facebook_image' => null,
            'dreisc_seo_twitter_description' => null,
            'dreisc_seo_twitter_image' => null,
            'dreisc_seo_robots_tag' => null,
            'dreisc_seo_sitemap_inactive' => null,
            'dreisc_seo_sitemap_priority' => null,
            'dreisc_seo_canonical_link_reference' => [],
            'dreisc_seo_canonical_link_type' => [],
        ];

        foreach($customFieldsKeys as $customFieldsKey => $type) {
            if(empty($customFields[$customFieldsKey])) {
                $customFields[$customFieldsKey] = $type;
            }
        }

        $productEntity->setCustomFields($customFields);
    }
}
