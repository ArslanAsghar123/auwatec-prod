<?php declare(strict_types=1);

namespace DreiscSeoPro\Subscriber\CategoryEvents;

use DreiscSeoPro\Core\Seo\LiveTemplate\LiveTemplateConverter;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Content\Category\CategoryEvents;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CategoryLoadedSubscriber implements EventSubscriberInterface
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
            CategoryEvents::CATEGORY_LOADED_EVENT => 'onCategoryLoaded'
        ];
    }

    public function onCategoryLoaded(EntityLoadedEvent $entityLoadedEvent): void
    {
        /** @var CategoryEntity $categoryEntity */
        $categoryEntity = current($entityLoadedEvent->getEntities());

        /** Abort, if empty */
        if (false === $categoryEntity) {
            return;
        }

        /** At this point the seo live template will be converted */
        $this->liveTemplateConverter->translateCategoryEntity($categoryEntity, $entityLoadedEvent->getContext());
    }
}
