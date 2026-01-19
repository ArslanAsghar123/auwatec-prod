<?php

namespace Rapidmail\Tests\Shopware\Unit\Subscribers;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Rapidmail\Shopware\Subscribers\DeletedEntitySubscriber;
use Shopware\Core\Checkout\Customer\CustomerEvents;
use Shopware\Core\Content\Product\ProductEvents;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityDeletedEvent;

class DeletedEntitySubscriberTest extends TestCase
{
    public function testSubscriberEventsAreConfiguredProperly(): void
    {
        $events = DeletedEntitySubscriber::getSubscribedEvents();

        $this->assertArrayHasKey(CustomerEvents::CUSTOMER_DELETED_EVENT, $events, 'Customers should be subscribed.');
        $this->assertArrayHasKey(ProductEvents::PRODUCT_DELETED_EVENT, $events, 'Product should be subscribed.');

        $subscriber = $this->createMock(DeletedEntitySubscriber::class);

        foreach ($events as $event => $methodName) {
            $this->assertTrue(
                method_exists($subscriber, $methodName),
                "Configured method `$methodName` does not exist."
            );
        }
    }

    public function testCreatingDeletedEntityWhenViaCustomerMethod(): void
    {
        $context = $this->createMock(Context::class);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('info');

        $entityRepository = $this->createMock(EntityRepository::class);
        $entityRepository
            ->expects($this->once())
            ->method('create')
            ->with(
                [
                    ['entityId' => 'a', 'type' => 'customer'],
                    ['entityId' => 'b', 'type' => 'customer'],
                ],
                $context
            );

        $subscriber = new DeletedEntitySubscriber(
            $entityRepository,
            $logger
        );

        $event = $this->createMock(EntityDeletedEvent::class);
        $event->expects($this->any())->method('getIds')->willReturn(['a', 'b']);
        $event->expects($this->any())->method('getContext')->willReturn($context);

        $subscriber->onDeleteCustomer(
            $event
        );
    }

    public function testCreatingDeletedEntityWhenViaProductMethod(): void
    {
        $context = $this->createMock(Context::class);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('info');

        $entityRepository = $this->createMock(EntityRepository::class);
        $entityRepository
            ->expects($this->once())
            ->method('create')
            ->with(
                [
                    ['entityId' => 'a', 'type' => 'product'],
                    ['entityId' => 'b', 'type' => 'product'],
                ],
                $context
            );

        $subscriber = new DeletedEntitySubscriber(
            $entityRepository,
            $logger
        );

        $event = $this->createMock(EntityDeletedEvent::class);
        $event->expects($this->any())->method('getIds')->willReturn(['a', 'b']);
        $event->expects($this->any())->method('getContext')->willReturn($context);

        $subscriber->onDeleteProduct(
            $event
        );
    }
}
