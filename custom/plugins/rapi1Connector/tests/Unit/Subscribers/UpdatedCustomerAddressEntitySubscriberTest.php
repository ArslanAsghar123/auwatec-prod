<?php

namespace Rapidmail\Tests\Shopware\Unit\Subscribers;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Rapidmail\Shopware\Subscribers\UpdatedCustomerAddressEntitySubscriber;
use Shopware\Core\Checkout\Customer\CustomerEvents;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\EntityWriteResult;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;

class UpdatedCustomerAddressEntitySubscriberTest extends TestCase
{
    public function testSubscriberEventsAreConfiguredProperly(): void
    {
        $events = UpdatedCustomerAddressEntitySubscriber::getSubscribedEvents();

        $this->assertArrayHasKey(CustomerEvents::CUSTOMER_ADDRESS_WRITTEN_EVENT, $events);

        $subscriber = $this->createMock(UpdatedCustomerAddressEntitySubscriber::class);

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
                ['customerId' => '123'],
                UpdatedCustomerAddressEntitySubscriber::SUBSCRIBED_TO_ENTITY_NAME,
                EntityWriteResult::OPERATION_UPDATE
            )
        ];
        $context = $this->createMock(Context::class);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('info');

        $customerRepository = $this->createMock(EntityRepository::class);

        $subscriber = new UpdatedCustomerAddressEntitySubscriber(
            $customerRepository,
            $logger
        );

        $event = $this->createMock(EntityWrittenEvent::class);
        $event->expects($this->any())->method('getWriteResults')->willReturn($writeResults);
        $event->expects($this->any())->method('getContext')->willReturn($context);
        $event->expects($this->any())->method('getEntityName')->willReturn(
            UpdatedCustomerAddressEntitySubscriber::SUBSCRIBED_TO_ENTITY_NAME
        );

        $subscriber->onWrittenAddress(
            $event
        );
    }
}
