<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\Ai;

use DreiscSeoPro\Core\CustomSetting\Struct\AbstractCustomSettingStruct;

class OpenAiStruct extends AbstractCustomSettingStruct
{
    /**
     * @var string|null
     */
    protected $apiKey;

    /**
     * @var string|null
     */
    protected $model;

    /**
     * @param string $defaultSalesChannelId
     * @param string $settingContext
     */
    public function __construct(?string $apiKey, ?string $model, string $settingContext)
    {
        parent::__construct($settingContext);

        $this->apiKey = $apiKey;
        $this->model = $model;
    }

    /**
     * @return string|null
     */
    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    /**
     * @param string|null $apiKey
     * @return OpenAiStruct
     */
    public function setApiKey(?string $apiKey): OpenAiStruct
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getModel(): ?string
    {
        return $this->model;
    }

    /**
     * @param string|null $model
     * @return OpenAiStruct
     */
    public function setModel(?string $model): OpenAiStruct
    {
        $this->model = $model;
        return $this;
    }
}
