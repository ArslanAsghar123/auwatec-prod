<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\SocialMedia;

use DreiscSeoPro\Core\CustomSetting\Struct\AbstractCustomSettingStruct;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\Common\LengthConfigStruct;

class FacebookDescriptionStruct extends AbstractCustomSettingStruct
{
    final const DEFAULT__LENGTH_CONFIG__RECOMMENDED_LENGTH_START = 10;
    final const DEFAULT__LENGTH_CONFIG__RECOMMENDED_LENGTH_END = 65;
    final const DEFAULT__LENGTH_CONFIG__MAX_LENGTH = 70;

    /**
     * @var LengthConfigStruct
     */
    protected $lengthConfig;

    /**
     * @param array $facebookDescriptionSettings
     * @param string $settingContext
     */
    public function __construct(array $facebookDescriptionSettings, string $settingContext)
    {
        parent::__construct($settingContext);

        $this->lengthConfig = new LengthConfigStruct(
            !empty($facebookDescriptionSettings['lengthConfig']['recommendedLengthStart']) ? $facebookDescriptionSettings['lengthConfig']['recommendedLengthStart'] : $this->setDefault(self::DEFAULT__LENGTH_CONFIG__RECOMMENDED_LENGTH_START),
            !empty($facebookDescriptionSettings['lengthConfig']['recommendedLengthEnd']) ? $facebookDescriptionSettings['lengthConfig']['recommendedLengthEnd'] : $this->setDefault(self::DEFAULT__LENGTH_CONFIG__RECOMMENDED_LENGTH_END),
            !empty($facebookDescriptionSettings['lengthConfig']['maxLength']) ? $facebookDescriptionSettings['lengthConfig']['maxLength'] : $this->setDefault(self::DEFAULT__LENGTH_CONFIG__MAX_LENGTH),
            $settingContext
        );
    }

    public function getLengthConfig(): LengthConfigStruct
    {
        return $this->lengthConfig;
    }

    public function setLengthConfig(LengthConfigStruct $lengthConfig): FacebookDescriptionStruct
    {
        $this->lengthConfig = $lengthConfig;

        return $this;
    }
}
