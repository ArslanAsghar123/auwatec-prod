<?php declare(strict_types=1);

namespace Intedia\Doofinder\Framework\Cookie;

use Psr\Log\LoggerInterface;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Framework\Cookie\CookieProviderInterface;

class DoofinderCookieProvider implements CookieProviderInterface
{
    const CONFIG_KEY = 'IntediaDoofinderSW6.config.';

    /** @var CookieProviderInterface */
    protected $originalService;

    /** @var SystemConfigService */
    protected $systemConfigService;

    /** @var LoggerInterface */
    protected $logger;

    /** @var array */
    protected $pluginConfig = [];

    public function __construct(CookieProviderInterface $service, LoggerInterface $logger, SystemConfigService $systemConfigService)
    {
        $this->originalService     = $service;
        $this->logger              = $logger;
        $this->systemConfigService = $systemConfigService;
    }

    /**
     * @return array
     */
    public function getCookieGroups(): array
    {
        return array_merge(
            $this->originalService->getCookieGroups(),
            [
                [
                    'snippet_name'        => 'intedia-doofinder.cookie.name',
                    'snippet_description' => 'intedia-doofinder.cookie.description',
                    'cookie'              => 'df-search-' . $this->getEngineHash(),
                    'isRequired'          => true
                ]
            ]
        );
    }

    /**
     * @return mixed
     */
    protected function getEngineHash()
    {
        $pluginConfig = $this->getPluginConfig();
        return array_key_exists(self::CONFIG_KEY . 'engineHashId', $pluginConfig) ? $pluginConfig[self::CONFIG_KEY . 'engineHashId'] : '';
    }

    /**
     * @return array
     */
    protected function getPluginConfig()
    {
        if (empty($this->pluginConfig)) {
            $this->pluginConfig = $this->systemConfigService->getDomain(self::CONFIG_KEY);
        }

        return $this->pluginConfig;
    }
}