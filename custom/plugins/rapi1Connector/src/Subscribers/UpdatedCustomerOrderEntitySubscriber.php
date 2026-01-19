<?php

declare(strict_types=1);

namespace Rapidmail\Shopware\Subscribers;

use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Order\Aggregate\OrderCustomer\OrderCustomerEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Order\OrderEvents;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\EntityWriteResult;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;

class UpdatedCustomerOrderEntitySubscriber extends UpdatedCustomerRelatedEntitySubscriber
{
    public const SUBSCRIBED_TO_ENTITY_NAME = 'order';

    private EntityRepository $orderRepository;

    public function __construct(
        EntityRepository $customerRepository,
        EntityRepository $orderRepository,
        LoggerInterface $logger
    ) {
        parent::__construct($customerRepository, $logger);
        $this->orderRepository = $orderRepository;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            OrderEvents::ORDER_WRITTEN_EVENT => 'onWrittenOrder',
        ];
    }

    public function onWrittenOrder(EntityWrittenEvent $event): void
    {
        $context = $event->getContext();
        $writeResult = $event->getWriteResults()[0] ?? null;

        if (!($writeResult instanceof EntityWriteResult)) {
            return;
        }

        $payload = $writeResult->getPayload();

        if ($this->isAllowed($event, $payload)) {
            $customerId = $this->getCustomerId($payload['id'], $context);

            if ($customerId) {
                $this->updateCustomer($customerId, $context);
            }
        }
    }

    protected function isAllowed(EntityWrittenEvent $event, ?array $data): bool
    {
        return (
            $event->getEntityName() === self::SUBSCRIBED_TO_ENTITY_NAME &&
            is_array($data) &&
            !empty($data['id'])
        );
    }

    private function getCustomerId(string $orderId, Context $context): ?string
    {
        $criteria = new Criteria([$orderId]);
        $criteria->addAssociation('orderCustomer');

        $order = $this->orderRepository
            ->search($criteria, $context)
            ->first();

        if ($order instanceof OrderEntity) {
            $orderCustomer = $order->getOrderCustomer();

            if ($orderCustomer instanceof OrderCustomerEntity) {
                return $orderCustomer->getCustomerId();
            }
        }

        return null;
    }
}
