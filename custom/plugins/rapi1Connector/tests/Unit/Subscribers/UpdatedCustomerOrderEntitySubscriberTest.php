<?php

namespace Rapidmail\Tests\Shopware\Unit\Subscribers;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Rapidmail\Shopware\Subscribers\UpdatedCustomerOrderEntitySubscriber;
use Shopware\Core\Checkout\Order\Aggregate\OrderCustomer\OrderCustomerEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Order\OrderEvents;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\EntityWriteResult;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;

class UpdatedCustomerOrderEntitySubscriberTest extends TestCase
{
    public function testSubscriberEventsAreConfiguredProperly(): void
    {
        $events = UpdatedCustomerOrderEntitySubscriber::getSubscribedEvents();

        $this->assertArrayHasKey(OrderEvents::ORDER_WRITTEN_EVENT, $events);

        $subscriber = $this->createMock(UpdatedCustomerOrderEntitySubscriber::class);

        foreach ($events as $methodName) {
            $this->assertTrue(
                method_exists($subscriber, $methodName),
                "Configured method `$methodName` does not exist."
            );
        }
    }

    public function testUpdatingCustomer(): void
    {
        $writeResults = [
            new EntityWriteResult(
                '123',
                ['id' => '123'],
                UpdatedCustomerOrderEntitySubscriber::SUBSCRIBED_TO_ENTITY_NAME,
                EntityWriteResult::OPERATION_UPDATE
            )
        ];
        $context = $this->createMock(Context::class);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('info');

        $customerRepository = $this->createMock(EntityRepository::class);
        $orderRepository = $this->createMock(EntityRepository::class);

        $orderCustomer = new OrderCustomerEntity();
        $orderCustomer->setOrderId('123');
        $orderCustomer->setCustomerId('456');

        $order = new OrderEntity();
        $order->setOrderCustomer($orderCustomer);

        $searchResult = $this->createMock(EntitySearchResult::class);
        $searchResult->expects($this->any())->method('first')->willReturn($order);
        $orderRepository->expects($this->any())->method('search')->willReturn($searchResult);

        $subscriber = new UpdatedCustomerOrderEntitySubscriber(
            $customerRepository,
            $orderRepository,
            $logger
        );

        $event = $this->createMock(EntityWrittenEvent::class);
        $event->expects($this->any())->method('getWriteResults')->willReturn($writeResults);
        $event->expects($this->any())->method('getContext')->willReturn($context);
        $event->expects($this->any())->method('getEntityName')->willReturn(
            UpdatedCustomerOrderEntitySubscriber::SUBSCRIBED_TO_ENTITY_NAME
        );

        $subscriber->onWrittenOrder(
            $event
        );
    }
}
