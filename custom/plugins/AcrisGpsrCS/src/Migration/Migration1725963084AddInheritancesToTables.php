<?php declare(strict_types=1);

namespace Acris\Gpsr\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Migration\InheritanceUpdaterTrait;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('core')]
class Migration1725963084AddInheritancesToTables extends MigrationStep
{
    use InheritanceUpdaterTrait;

    public function getCreationTimestamp(): int
    {
        return 1725963084;
    }

    public function update(Connection $connection): void
    {
        if (!$this->columnExists($connection, 'rule', 'acrisGpsrNotes')) {
            $this->updateInheritance($connection, 'rule', 'acrisGpsrNotes');
        }
        if (!$this->columnExists($connection, 'sales_channel', 'acrisGpsrNotes')) {
            $this->updateInheritance($connection, 'sales_channel', 'acrisGpsrNotes');
        }
        if (!$this->columnExists($connection, 'product_stream', 'acrisGpsrNotes')) {
            $this->updateInheritance($connection, 'product_stream', 'acrisGpsrNotes');
        }
        if (!$this->columnExists($connection, 'rule', 'acrisGpsrManufacturers')) {
            $this->updateInheritance($connection, 'rule', 'acrisGpsrManufacturers');
        }
        if (!$this->columnExists($connection, 'sales_channel', 'acrisGpsrManufacturers')) {
            $this->updateInheritance($connection, 'sales_channel', 'acrisGpsrManufacturers');
        }

        if (!$this->columnExists($connection, 'product_stream', 'acrisGpsrManufacturers')) {
            $this->updateInheritance($connection, 'product_stream', 'acrisGpsrManufacturers');
        }

        if (!$this->columnExists($connection, 'rule', 'acrisGpsrContacts')) {
            $this->updateInheritance($connection, 'rule', 'acrisGpsrContacts');
        }

        if (!$this->columnExists($connection, 'sales_channel', 'acrisGpsrContacts')) {
            $this->updateInheritance($connection, 'sales_channel', 'acrisGpsrContacts');
        }

        if (!$this->columnExists($connection, 'product_stream', 'acrisGpsrContacts')) {
            $this->updateInheritance($connection, 'product_stream', 'acrisGpsrContacts');
        }

    }
}
