<?php declare(strict_types=1);

namespace DreiscSeoPro\Subscriber\CategoryEvents;

use DreiscSeoPro\Core\Cache\MessageBusCacheInvalidator;
use Shopware\Core\Content\Category\CategoryEvents;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CategoryWrittenSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly MessageBusCacheInvalidator $messageBusCacheInvalidator
    ) { }

    /**
     * @return array|void
     */
    public static function getSubscribedEvents(): array
    {
        return [
            CategoryEvents::CATEGORY_WRITTEN_EVENT => 'onCategoryWritten',
            'dreisc_seo_bulk.written' => 'onCategoryWritten',
            'dreisc_seo_bulk_template.written' => 'onCategoryWritten'
        ];
    }

    public function onCategoryWritten(EntityWrittenEvent $entityWrittenEvent): void
    {
        $this->messageBusCacheInvalidator->updateLastCacheTimestamp();
    }
}
