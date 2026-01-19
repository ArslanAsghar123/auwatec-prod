<?php

namespace Rapidmail\Tests\Shopware\Unit\Repositories;

use PHPUnit\Framework\TestCase;
use Rapidmail\Shopware\Repositories\IntegrationRepository;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;

class IntegrationRepositoryTest extends TestCase
{
    public function testCreateIntegration(): void
    {
        $newIntegrationId = 321;

        $context = $this->createMock(Context::class);

        $event = $this->createMock(EntityWrittenContainerEvent::class);
        $event->expects($this->any())->method('getPrimaryKeys')->willReturn([$newIntegrationId]);

        $entityRepository = $this->createMock(EntityRepository::class);
        $entityRepository
            ->expects($this->once())
            ->method('upsert')
            ->with($this->anything(), $context)
            ->willReturn($event);

        $repository = new IntegrationRepository($entityRepository);

        $entity = $repository->createIntegration($context);

        $this->assertMatchesRegularExpression(
            '/([0-9A-Z]{26})/',
            $entity->getAccessKey(),
            'Access key is not as expected.'
        );
        $this->assertMatchesRegularExpression(
            '/([0-9A-Z]{51})/i',
            $entity->getSecretAccessKey(),
            'Secret access is not as expected.'
        );
        $this->assertStringStartsWith(IntegrationRepository::LABEL, $entity->getLabel(), 'Label is not as expected.');
    }
}
