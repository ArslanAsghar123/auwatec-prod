<?php

declare(strict_types=1);

namespace Rapidmail\Shopware\Subscribers;

use Shopware\Core\Checkout\Customer\CustomerEvents;
use Shopware\Core\Framework\DataAbstractionLayer\EntityWriteResult;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;

class UpdatedCustomerAddressEntitySubscriber extends UpdatedCustomerRelatedEntitySubscriber
{
    public const SUBSCRIBED_TO_ENTITY_NAME = 'customer_address';

    public static function getSubscribedEvents(): array
    {
        return [
            CustomerEvents::CUSTOMER_ADDRESS_WRITTEN_EVENT => 'onWrittenAddress',
        ];
    }

    public function onWrittenAddress(EntityWrittenEvent $event): void
    {
        $context = $event->getContext();
        $writeResult = $event->getWriteResults()[0] ?? null;

        if (!($writeResult instanceof EntityWriteResult)) {
            return;
        }

        $payload = $writeResult->getPayload();

        if ($this->isAllowed($event, $payload)) {
            $this->updateCustomer(
                $this->getCustomerId($payload),
                $context
            );
        }
    }

    protected function isAllowed(EntityWrittenEvent $event, ?array $data): bool
    {
        return (
            $event->getEntityName() === self::SUBSCRIBED_TO_ENTITY_NAME &&
            is_array($data) &&
            !empty($this->getCustomerId($data))
        );
    }

    private function getCustomerId(array $data): ?string
    {
        return (!empty($data['customerId'])) ? $data['customerId'] : null;
    }
}
