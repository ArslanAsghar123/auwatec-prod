<?php declare(strict_types=1);

namespace Swpa\SwpaBackup\Test\Mock\Repositories;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

/**
 * Entity Definition Mock
 *
 * @package   Swpa\SwpaBackup\Test\Mock
 * @copyright See COPYING.txt for license details
 * @author    swpa <info@swpa.dev>
 */
class EntityDefinitionMock extends EntityDefinition
{
    public function getEntityName(): string
    {
        return 'entity';
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([]);
    }
}
