<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\SocialMedia;

use DreiscSeoPro\Core\CustomSetting\Struct\AbstractCustomSettingStruct;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\Common\LengthConfigStruct;

class TwitterDescriptionStruct extends AbstractCustomSettingStruct
{
    final const DEFAULT__LENGTH_CONFIG__RECOMMENDED_LENGTH_START = 10;
    final const DEFAULT__LENGTH_CONFIG__RECOMMENDED_LENGTH_END = 110;
    final const DEFAULT__LENGTH_CONFIG__MAX_LENGTH = 120;

    /**
     * @var LengthConfigStruct
     */
    protected $lengthConfig;

    /**
     * @param array $twitterDescriptionSettings
     * @param string $settingContext
     */
    public function __construct(array $twitterDescriptionSettings, string $settingContext)
    {
        parent::__construct($settingContext);

        $this->lengthConfig = new LengthConfigStruct(
            !empty($twitterDescriptionSettings['lengthConfig']['recommendedLengthStart']) ? $twitterDescriptionSettings['lengthConfig']['recommendedLengthStart'] : $this->setDefault(self::DEFAULT__LENGTH_CONFIG__RECOMMENDED_LENGTH_START),
            !empty($twitterDescriptionSettings['lengthConfig']['recommendedLengthEnd']) ? $twitterDescriptionSettings['lengthConfig']['recommendedLengthEnd'] : $this->setDefault(self::DEFAULT__LENGTH_CONFIG__RECOMMENDED_LENGTH_END),
            !empty($twitterDescriptionSettings['lengthConfig']['maxLength']) ? $twitterDescriptionSettings['lengthConfig']['maxLength'] : $this->setDefault(self::DEFAULT__LENGTH_CONFIG__MAX_LENGTH),
            $settingContext
        );
    }

    public function getLengthConfig(): LengthConfigStruct
    {
        return $this->lengthConfig;
    }

    public function setLengthConfig(LengthConfigStruct $lengthConfig): TwitterDescriptionStruct
    {
        $this->lengthConfig = $lengthConfig;

        return $this;
    }
}
