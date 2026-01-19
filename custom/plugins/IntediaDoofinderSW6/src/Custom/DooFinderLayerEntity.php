<?php declare(strict_types=1);

namespace Intedia\Doofinder\Custom;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\Framework\DataAbstractionLayer\Field\DateField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;

class DooFinderLayerEntity extends Entity
{
    use EntityIdTrait;

    protected ?string $doofinderChannelId;
    protected ?string $doofinderHashId;
    protected ?string $doofinderStoreId;
    protected ?string $domainId;
    protected ?string $name;
    protected ?string $status;
    protected ?string $trigger;
    protected ?string $statusMessage;
    protected ?string $statusDate;
    protected ?string $statusReceivedDate;

    public function getStatusMessage(): ?string
    {
        return $this->statusMessage;
    }

    public function setStatusMessage(?string $statusMessage): void
    {
        $this->statusMessage = $statusMessage;
    }

    public function getDoofinderChannelId(): ?string
    {
        return $this->doofinderChannelId;
    }

    public function setDoofinderChannelId(string $doofinderChannelId): void
    {
        $this->doofinderChannelId = $doofinderChannelId;
    }

    public function getDooFinderHashId(): ?string
    {
        return $this->doofinderHashId;
    }

    public function setDoofinderHashId(?string $doofinderHashId): void
    {
        $this->doofinderHashId = $doofinderHashId;
    }

    public function setDomainId(string $domainId): void
    {
        $this->domainId = $domainId;
    }

    public function getDomainId(): ?string
    {
        return $this->domainId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): void
    {
        $this->status = $status;
    }

    public function getStatusDate(): ?string
    {
        return $this->statusDate;
    }

    public function setStatusDate(?string $statusDate): void
    {
        $this->statusDate = $statusDate;
    }

    public function getDoofinderStoreId(): ?string
    {
        return $this->doofinderStoreId;
    }

    public function setDoofinderStoreId(?string $doofinderStoreId): void
    {
        $this->doofinderStoreId = $doofinderStoreId;
    }

    public function getTrigger(): ?string
    {
        return $this->trigger;
    }

    public function setTrigger(?string $trigger): void
    {
        $this->trigger = $trigger;
    }

    public function getStatusReceivedDate(): ?string
    {
        return $this->statusReceivedDate;
    }

    public function setStatusReceivedDate(?string $statusReceivedDate): void
    {
        $this->statusReceivedDate = $statusReceivedDate;
    }
}