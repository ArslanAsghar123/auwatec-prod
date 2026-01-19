<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\MetaTags;

use DreiscSeoPro\Core\CustomSetting\Struct\AbstractCustomSettingStruct;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\Common\LengthConfigStruct;

class MetaDescriptionStruct extends AbstractCustomSettingStruct
{
    final const DEFAULT__LENGTH_CONFIG__RECOMMENDED_LENGTH_START = 35;
    final const DEFAULT__LENGTH_CONFIG__RECOMMENDED_LENGTH_END = 130;
    final const DEFAULT__LENGTH_CONFIG__MAX_LENGTH = 160;

    /**
     * @var LengthConfigStruct
     */
    protected $lengthConfig;

    /**
     * @param array $keywordsSettings
     * @param string $settingContext
     */
    public function __construct(array $keywordsSettings, string $settingContext)
    {
        parent::__construct($settingContext);

        $this->lengthConfig = new LengthConfigStruct(
            !empty($keywordsSettings['lengthConfig']['recommendedLengthStart']) ? $keywordsSettings['lengthConfig']['recommendedLengthStart'] : $this->setDefault(self::DEFAULT__LENGTH_CONFIG__RECOMMENDED_LENGTH_START),
            !empty($keywordsSettings['lengthConfig']['recommendedLengthEnd']) ? $keywordsSettings['lengthConfig']['recommendedLengthEnd'] : $this->setDefault(self::DEFAULT__LENGTH_CONFIG__RECOMMENDED_LENGTH_END),
            !empty($keywordsSettings['lengthConfig']['maxLength']) ? $keywordsSettings['lengthConfig']['maxLength'] : $this->setDefault(self::DEFAULT__LENGTH_CONFIG__MAX_LENGTH),
            $settingContext
        );
    }

    public function getLengthConfig(): LengthConfigStruct
    {
        return $this->lengthConfig;
    }

    /**
     * @return MetaDescriptionStruct
     */
    public function setLengthConfig(LengthConfigStruct $lengthConfig): MetaTitleStruct
    {
        $this->lengthConfig = $lengthConfig;

        return $this;
    }
}
