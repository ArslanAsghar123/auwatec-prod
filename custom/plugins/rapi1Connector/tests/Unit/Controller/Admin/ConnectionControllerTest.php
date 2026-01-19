<?php

namespace Rapidmail\Tests\Shopware\Unit\Controller\Admin;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Rapidmail\Shopware\Controller\Admin\ConnectionController;
use Rapidmail\Shopware\Repositories\IntegrationRepository;
use Rapidmail\Shopware\Services\Encrypter;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\Integration\IntegrationEntity;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class ConnectionControllerTest extends TestCase
{
    public function testCreateCredentialsActions(): void
    {
        $encryptedString = 'encryptedstring';
        $shopUrl = 'https://www.foo.test';
        $connectUrl = 'https://connection.test';
        $shopVersion = '6.2.1.0';
        $accessKey = 'myaccesskey';
        $secretAccessKey = 'mysecretaccesskey';

        $context = $this->createMock(Context::class);

        $integration = new IntegrationEntity();
        $integration->setAccessKey($accessKey);
        $integration->setSecretAccessKey($secretAccessKey);

        $integrationRepository = $this->createMock(IntegrationRepository::class);
        $integrationRepository
            ->expects($this->once())
            ->method('createIntegration')
            ->with($context)
            ->willReturn($integration);

        $configService = $this->createMock(SystemConfigService::class);
        $configService
            ->expects($this->any())
            ->method('get')
            ->with('rapi1Connector.config.connectUrl')
            ->willReturn($connectUrl);

        $encrypter = $this->createMock(Encrypter::class);
        $encrypter
            ->expects($this->once())
            ->method('encrypt')
            ->with(
                [
                    'payload' => compact('accessKey', 'secretAccessKey'),
                    'shop' => [
                        'type' => 'shopware',
                        'version' => $shopVersion,
                        'url' => $shopUrl,
                        'apiVersion' => 3,
                    ],
                ]
            )
            ->willReturn($encryptedString);

        $controller = new ConnectionController(
            $integrationRepository,
            $configService,
            $encrypter,
            $shopUrl
        );

        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects($this->any())
            ->method('getParameter')
            ->with('kernel.shopware_version')
            ->willReturn($shopVersion);

        $controller->setContainer($container);

        $response = $controller->createCredentials($context);

        $this->assertInstanceOf(JsonResponse::class, $response, 'Response should be JSON.');

        $data = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('url', $data, 'Response is not complete (missing: url).');
        $this->assertEquals($encryptedString, $data['connection'], 'Response is not complete (missing: connection).');
    }

    public function testInformationAction(): void
    {
        $url = 'https://www.foo.test/lol';
        $configKey = 'rapi1Connector.config.overviewUrl';

        $configService = $this->createMock(SystemConfigService::class);
        $configService->expects($this->once())->method('get')->with($configKey)->willReturn($url);

        $controller = new ConnectionController(
            $this->createMock(IntegrationRepository::class),
            $configService,
            $this->createMock(Encrypter::class),
            'https://myshop.test'
        );

        $controller->setLogger($this->createMock(LoggerInterface::class));
        $controller->setContainer($this->createMock(ContainerInterface::class));

        $response = $controller->information();

        $this->assertInstanceOf(JsonResponse::class, $response, 'Response should be JSON.');

        $data = json_decode($response->getContent(), true);

        $this->assertEquals($url, $data['overviewUrl'] ?? null, 'Overview url is not as expected.');
    }
}
