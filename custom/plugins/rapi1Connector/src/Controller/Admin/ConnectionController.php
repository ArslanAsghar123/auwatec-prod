<?php

namespace Rapidmail\Shopware\Controller\Admin;

use Psr\Log\LoggerAwareTrait;
use Rapidmail\Shopware\Services\Encrypter;
use Rapidmail\Shopware\Services\PluginInfo;
use Rapidmail\Shopware\Services\Rapi1User as Rapi1UserService;
use Shopware\Core\Framework\Context;
use Shopware\Core\PlatformRequest;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * added double routes to be backwards compatible for sw < 6.4
 */
class ConnectionController extends AbstractController
{
    use LoggerAwareTrait;

    private SystemConfigService $configService;
    private Rapi1UserService $rapi1UserService;
    private Encrypter $encrypter;
    private ?string $shopUrl;
    private PluginInfo $pluginInfo;

    public function __construct(
        SystemConfigService $configService,
        Rapi1UserService $rapi1UserService,
        Encrypter $encrypter,
        ?string $shopUrl,
        PluginInfo $pluginInfo
    ) {
        $this->configService = $configService;
        $this->rapi1UserService = $rapi1UserService;
        $this->encrypter = $encrypter;
        $this->shopUrl = $shopUrl;
        $this->pluginInfo = $pluginInfo;
    }

    #[Route(
        path: 'api/rapidmail/connection/info',
        name: "api.rapidmail.connection.info",
        defaults: ['_routeScope' => ['api']],
        methods: ['GET']
    )]
    public function information(): JsonResponse
    {
        return $this->json([
            'overviewUrl' => $this->configService->get('rapi1Connector.config.overviewUrl'),
        ]);
    }

    #[Route(
        path: 'api/rapidmail/connection/credentials',
        name: "api.rapidmail.connection.credentials.create",
        defaults: ['_routeScope' => ['api']],
        methods: ['POST']
    )]
    public function createCredentials(Context $context): JsonResponse
    {
        $userAccessKey = $this->rapi1UserService->createAccessKey($context);

        $payload = [
            'accessKey' => $userAccessKey->getAccessKey(),
            'secretAccessKey' => $userAccessKey->getSecretAccessKey(),
        ];

        $url = $this->configService->get('rapi1Connector.config.connectUrl');

        $shop = $this->getShopData();

        $connection = $this->encrypter->encrypt(compact('payload', 'shop'));

        return $this->json(
            compact('url', 'connection', 'payload', 'shop')
        );
    }

    protected function getShopData(): array
    {
        return [
            'type' => 'shopware',
            'version' => $this->container->getParameter('kernel.shopware_version'),
            'url' => $this->shopUrl ?? $_SERVER['APP_URL'],
            'apiVersion' => defined('\Shopware\Core\PlatformRequest::API_VERSION') ?
                (string)PlatformRequest::API_VERSION :
                null,
        ];
    }

    #[Route(
        path: 'api/rapidmail/connection/system_status',
        name: "api.rapidmail.connection.system_status",
        defaults: ['_routeScope' => ['api']],
        methods: ['GET']
    )]
    public function systemStatus(): JsonResponse
    {
        $data = [
            'attributes.environment' => [
                'shop_version' => $this->container->getParameter('kernel.shopware_version'),
                'plugin_version' => $this->pluginInfo->getVersion(),
            ],
        ];

        return $this->json(compact('data'));
    }
}
