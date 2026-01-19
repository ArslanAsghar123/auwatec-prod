<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\CustomSetting\Struct;

use DreiscSeoPro\Core\Foundation\Struct\DefaultStruct;

class AbstractCustomSettingStruct extends DefaultStruct
{
    /**
     * @var string
     */
    protected $settingContext;

    /**
     * @param string $settingContext
     */
    public function __construct(string $settingContext)
    {
        $this->settingContext = $settingContext;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $jsonArray = [];
        foreach (get_object_vars($this) as $key => $value) {
            if ($value instanceof AbstractCustomSettingStruct) {
                $jsonArray[$key] = $value->toArray();
            } elseif (is_object($value)) {
                throw new \RuntimeException('Make sure that the following class extends AbstractCustomSettingStruct » ' . $value::class);
            } else {
                if ('extensions' === $key) {
                    continue;
                }

                $jsonArray[$key] = $value;
            }
        }

        /** Unset settingContext key */
        if (isset($jsonArray['settingContext'])) {
            unset($jsonArray['settingContext']);
        }

        return $jsonArray;
    }

    /**
     * @param $default
     * @return mixed|null
     */
    protected function setDefault($default)
    {
        if (CustomSettingStruct::SETTING_CONTEXT__SALES_CHANNEL === $this->settingContext) {
            return null;
        }

        return $default;
    }
}
