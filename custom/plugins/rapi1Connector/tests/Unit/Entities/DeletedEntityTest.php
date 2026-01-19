<?php

namespace Rapidmail\Tests\Shopware\Unit\Entities;

use PHPUnit\Framework\TestCase;
use Rapidmail\Shopware\Entities\DeletedEntity;

class DeletedEntityTest extends TestCase
{
    public function testGetterAndSetters(): void
    {
        $entity = new DeletedEntity();

        $entity->setId('foo');
        $entity->setEntityId('bar');
        $entity->setType('baz');

        $this->assertEquals('foo', $entity->getId(), 'Id getter is not working as expected.');
        $this->assertEquals('bar', $entity->getEntityId(), 'Entity id getter is not working as expected.');
        $this->assertEquals('baz', $entity->getType(), 'Type getter is not working as expected.');
    }
}
