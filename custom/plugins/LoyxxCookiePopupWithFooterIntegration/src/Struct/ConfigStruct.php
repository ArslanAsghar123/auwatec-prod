<?php
namespace LoyxxCookiePopupWithFooterIntegration\Struct;

use Shopware\Core\Framework\Struct\Struct;

class ConfigStruct extends Struct
{

    /**
     * @var string
     */
    private $configs;

    public function __construct(array $configs)
    {
        $this->configs = $configs;
    }

    /**
     * @return array
     */
    public function getConfigs(): array
    {
        return $this->configs;
    }

    /**
     * @param array $config
     */
    public function setConfigs(array $configs): void
    {
        $this->configs = $configs;
    }
}
