<?php declare(strict_types=1);

namespace Acris\Gpsr\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('core')]
class Migration1732705240RemoveInherance extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1732705240;
    }

    public function update(Connection $connection): void
    {

        if ($this->columnExists($connection, 'media', 'acrisGpsrContactDownloads')) {
            $this->removeInheritance($connection, 'media', 'acrisGpsrContactDownloads');
        }

        if ($this->columnExists($connection, 'media', 'acrisGpsrNoteDownloads')) {
            $this->removeInheritance($connection, 'media', 'acrisGpsrNoteDownloads');
        }

        if ($this->columnExists($connection, 'media', 'acrisGpsrManufacturerDownloads')) {
            $this->removeInheritance($connection, 'media', 'acrisGpsrManufacturerDownloads');
        }

        if ($this->columnExists($connection, 'media', 'acrisManufacturerDownloads')) {
            $this->removeInheritance($connection, 'media', 'acrisManufacturerDownloads');
        }
        if ($this->columnExists($connection, 'media', 'acrisGpsrNotes')) {
            $this->removeInheritance($connection, 'media', 'acrisGpsrNotes');
        }

    }

    private function removeInheritance(Connection $connection, string $entity, string $propertyName): void
    {
        $sql = str_replace(
            ['#table#', '#column#'],
            [$entity, $propertyName],
            'ALTER TABLE `#table#` DROP `#column#`'
        );

        $connection->executeStatement($sql);
    }
}
