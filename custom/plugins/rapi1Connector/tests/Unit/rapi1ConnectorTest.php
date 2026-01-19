<?php

namespace Rapidmail\Tests\Shopware\Unit;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Rapidmail\Shopware\rapi1Connector;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Symfony\Component\DependencyInjection\ContainerInterface;

class rapi1ConnectorTest extends TestCase
{
    public function testUninstallMethodWithoutKeepingUserData(): void
    {
        /** @var rapi1Connector $connector */
        $connector = $this->createPartialMock(rapi1Connector::class, []);

        $context = $this->createMock(Context::class);

        $uninstallContext = $this->createMock(UninstallContext::class);
        $uninstallContext->expects($this->any())->method('keepUserData')->willReturn(false);
        $uninstallContext->expects($this->any())->method('getContext')->willReturn($context);

        $connection = $this->createMock(Connection::class);
        $connection->expects($this->exactly(2))->method('executeUpdate');

        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects($this->any())
            ->method('get')
            ->willReturnMap(
                [
                    [Connection::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $connection],
                ]
            );

        $connector->setContainer($container);
        $connector->uninstall($uninstallContext);
    }

    public function testUninstallMethodAndKeepingUserData(): void
    {
        /** @var rapi1Connector $connector */
        $connector = $this->createPartialMock(rapi1Connector::class, []);

        $uninstallContext = $this->createMock(UninstallContext::class);
        $uninstallContext->expects($this->any())->method('keepUserData')->willReturn(true);

        $connection = $this->createMock(Connection::class);
        $connection->expects($this->never())->method('executeUpdate');

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->any())->method('get')->willReturnMap(
            [
                [Connection::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $connection],
            ]
        );

        $connector->setContainer($container);
        $connector->uninstall($uninstallContext);
    }
}
