<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting;

use DreiscSeoPro\Core\CustomSetting\Struct\AbstractCustomSettingStruct;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\Ai\OpenAiStruct;

class AiStruct extends AbstractCustomSettingStruct
{
    /**
     * @var OpenAiStruct
     */
    protected $openAi;

    /**
     * @param string $defaultSalesChannelId
     * @param string $settingContext
     */
    public function __construct(array $aiSettings, string $settingContext)
    {
        parent::__construct($settingContext);

        $this->openAi = new OpenAiStruct(
            !empty($aiSettings['openAi']['apiKey']) ? $aiSettings['openAi']['apiKey'] : null,
            !empty($aiSettings['openAi']['model']) ? $aiSettings['openAi']['model'] : null,
            $settingContext
        );
    }

    /**
     * @return OpenAiStruct
     */
    public function getOpenAi(): OpenAiStruct
    {
        return $this->openAi;
    }

    /**
     * @param OpenAiStruct $openAi
     * @return AiStruct
     */
    public function setOpenAi(OpenAiStruct $openAi): AiStruct
    {
        $this->openAi = $openAi;
        return $this;
    }
}
