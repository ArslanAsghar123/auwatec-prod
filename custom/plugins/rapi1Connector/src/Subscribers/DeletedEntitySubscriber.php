<?php

declare(strict_types=1);

namespace Rapidmail\Shopware\Subscribers;

use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Customer\CustomerEvents;
use Shopware\Core\Content\Newsletter\NewsletterEvents;
use Shopware\Core\Content\Product\ProductEvents;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityDeletedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DeletedEntitySubscriber implements EventSubscriberInterface
{
    private $entityRepository;
    private $logger;

    public function __construct(
        EntityRepository $entityRepository,
        LoggerInterface $logger
    ) {
        $this->entityRepository = $entityRepository;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents()
    {
        return [
            CustomerEvents::CUSTOMER_DELETED_EVENT => 'onDeleteCustomer',
            ProductEvents::PRODUCT_DELETED_EVENT => 'onDeleteProduct',
            NewsletterEvents::NEWSLETTER_RECIPIENT_DELETED_EVENT => 'onDeleteNewsletterRecipient',
        ];
    }

    public function onDeleteCustomer(EntityDeletedEvent $event): void
    {
        $this->createDeletedEntity('customer', $event->getIds(), $event->getContext());
    }

    public function onDeleteProduct(EntityDeletedEvent $event): void
    {
        $this->createDeletedEntity('product', $event->getIds(), $event->getContext());
    }

    public function onDeleteNewsletterRecipient(EntityDeletedEvent $event): void
    {
        $this->createDeletedEntity('newsletter_recipient', $event->getIds(), $event->getContext());
    }

    /**
     * @param string[] $ids
     */
    private function createDeletedEntity(string $type, array $ids, Context $context): void
    {
        $this->logger->info("Track deleted $type", compact('ids'));

        $this->entityRepository->create(
            array_map(
                function (string $id) use ($type) {
                    return [
                        'entityId' => (string)$id,
                        'type' => $type,
                    ];
                },
                $ids
            ),
            $context
        );
    }
}
