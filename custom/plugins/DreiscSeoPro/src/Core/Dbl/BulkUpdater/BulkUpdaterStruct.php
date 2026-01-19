<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Dbl\BulkUpdater;

use DreiscSeoPro\Core\Foundation\Dal\EntityRepository;
use DreiscSeoPro\Core\Foundation\Struct\DefaultStruct;

class BulkUpdaterStruct extends DefaultStruct
{
    /**
     * @var ?EntityRepository
     */
    protected $entityRepository;

    /**
     * @var array
     */
    protected $updates;

    public function __construct(?EntityRepository $entityRepository = null, ?array $updates = [])
    {
        $this->entityRepository = $entityRepository;
        $this->updates = $updates;
    }

    public function getEntityRepository(): ?EntityRepository
    {
        return $this->entityRepository;
    }

    public function setEntityRepository(EntityRepository $entityRepository): BulkUpdaterStruct
    {
        $this->entityRepository = $entityRepository;

        return $this;
    }

    public function setEntityRepositoryIfNull(EntityRepository $entityRepository): BulkUpdaterStruct
    {
        if (null !== $this->entityRepository) {
            return $this;
        }

        $this->entityRepository = $entityRepository;

        return $this;
    }

    public function getUpdates(): array
    {
        return $this->updates;
    }

    public function setUpdates(array $updates): BulkUpdaterStruct
    {
        $this->updates = $updates;

        return $this;
    }

    public function addUpdate(array $update): void
    {
        $this->updates[] = $update;
    }
}
