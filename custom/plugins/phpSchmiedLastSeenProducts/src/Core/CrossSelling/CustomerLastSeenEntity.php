<?php declare(strict_types=1);
namespace phpSchmied\LastSeenProducts\Core\CrossSelling;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;

class CustomerLastSeenEntity extends Entity
{
    protected string $productId;
    protected string $customerId;
    protected \DateTime $last_view;

    public function getLastView(): \DateTime
    {
        return $this->last_view;
    }

    public function setLastView(\DateTime $last_view): void
    {
        $this->last_view = $last_view;
    }

    public function getProductId(): string
    {
        return $this->productId;
    }

    public function setProductId(string $productId): void
    {
        $this->productId = $productId;
    }

    public function getCustomerId(): string
    {
        return $this->customerId;
    }

    public function setCustomerId(string $customerId): void
    {
        $this->customerId = $customerId;
    }
}
