<?php

namespace Rapidmail\Shopware\Subscribers;

use Psr\Log\LoggerInterface;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

abstract class UpdatedCustomerRelatedEntitySubscriber implements EventSubscriberInterface
{
    public const SUBSCRIBED_TO_ENTITY_NAME = null;

    private EntityRepository $customerRepository;
    private LoggerInterface $logger;

    public function __construct(
        EntityRepository $customerRepository,
        LoggerInterface $logger
    ) {
        $this->customerRepository = $customerRepository;
        $this->logger = $logger;
    }

    abstract protected function isAllowed(EntityWrittenEvent $event, ?array $data): bool;

    protected function updateCustomer(string $customerId, Context $context): void
    {
        try {
            $this->logger->info("Customer updated: " . $customerId);

            $updateData = [
                'id' => $customerId,
                'updated_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            ];

            $this->customerRepository->update([$updateData], $context);
        } catch (\Exception $exception) {
            $this->logger->error("Customer update error: " . $exception->getMessage());
        }
    }
}
