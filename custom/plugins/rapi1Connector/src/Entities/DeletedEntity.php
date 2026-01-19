<?php

declare(strict_types=1);

namespace Rapidmail\Shopware\Entities;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class DeletedEntity extends Entity
{
    use EntityIdTrait;

    /**
     * @var string
     */
    protected $type;
    /**
     * @var string
     */
    protected $entityId;

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getEntityId(): string
    {
        return $this->entityId;
    }

    public function setEntityId(string $entityId): void
    {
        $this->entityId = $entityId;
    }
}