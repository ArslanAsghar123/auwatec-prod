<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\SocialMedia;

use DreiscSeoPro\Core\CustomSetting\Struct\AbstractCustomSettingStruct;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\Common\LengthConfigStruct;

class TwitterTitleStruct extends AbstractCustomSettingStruct
{
    final const DEFAULT__LENGTH_CONFIG__RECOMMENDED_LENGTH_START = 10;
    final const DEFAULT__LENGTH_CONFIG__RECOMMENDED_LENGTH_END = 35;
    final const DEFAULT__LENGTH_CONFIG__MAX_LENGTH = 40;

    /**
     * @var LengthConfigStruct
     */
    protected $lengthConfig;

    /**
     * @param array $twitterTitleSettings
     * @param string $settingContext
     */
    public function __construct(array $twitterTitleSettings, string $settingContext)
    {
        parent::__construct($settingContext);

        $this->lengthConfig = new LengthConfigStruct(
            !empty($twitterTitleSettings['lengthConfig']['recommendedLengthStart']) ? $twitterTitleSettings['lengthConfig']['recommendedLengthStart'] : $this->setDefault(self::DEFAULT__LENGTH_CONFIG__RECOMMENDED_LENGTH_START),
            !empty($twitterTitleSettings['lengthConfig']['recommendedLengthEnd']) ? $twitterTitleSettings['lengthConfig']['recommendedLengthEnd'] : $this->setDefault(self::DEFAULT__LENGTH_CONFIG__RECOMMENDED_LENGTH_END),
            !empty($twitterTitleSettings['lengthConfig']['maxLength']) ? $twitterTitleSettings['lengthConfig']['maxLength'] : $this->setDefault(self::DEFAULT__LENGTH_CONFIG__MAX_LENGTH),
            $settingContext
        );
    }

    public function getLengthConfig(): LengthConfigStruct
    {
        return $this->lengthConfig;
    }

    public function setLengthConfig(LengthConfigStruct $lengthConfig): TwitterTitleStruct
    {
        $this->lengthConfig = $lengthConfig;

        return $this;
    }
}
