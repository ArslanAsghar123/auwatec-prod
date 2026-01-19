<?php declare(strict_types=1);

namespace Swpa\SwpaBackup\DAL\Backup;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * Backup entity collection
 *
 * @package   Swpa\SwpaBackup\DAL\Backup
 * @copyright See COPYING.txt for license details
 * @author    swpa <info@swpa.dev>
 */
class SwpaBackupCollection extends EntityCollection
{
    /**
     * @return string
     */
    protected function getExpectedClass(): string
    {
        return SwpaBackupEntity::class;
    }
}
