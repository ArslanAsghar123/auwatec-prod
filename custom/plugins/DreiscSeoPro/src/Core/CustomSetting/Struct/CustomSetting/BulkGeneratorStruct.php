<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting;


use DreiscSeoPro\Core\CustomSetting\Struct\AbstractCustomSettingStruct;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\BulkGenerator\GeneralStruct;

class BulkGeneratorStruct extends AbstractCustomSettingStruct
{
    final const DEFAULT__GENERAL_START_GENERATOR_IN_THE_STORAGE_PROCESS = true;

    /**
     * @var GeneralStruct
     */
    protected $general;

    /**
     * @param array $bulkGeneratorSettings
     * @param string $settingContext
     */
    public function __construct(array $bulkGeneratorSettings, string $settingContext)
    {
        parent::__construct($settingContext);

        $this->general = new GeneralStruct(
            isset($bulkGeneratorSettings['general']['startGeneratorInTheStorageProcess']) && is_bool($bulkGeneratorSettings['general']['startGeneratorInTheStorageProcess']) ? $bulkGeneratorSettings['general']['startGeneratorInTheStorageProcess'] : $this->setDefault(self::DEFAULT__GENERAL_START_GENERATOR_IN_THE_STORAGE_PROCESS),
            $settingContext
        );
    }

    public function getGeneral(): GeneralStruct
    {
        return $this->general;
    }

    public function setGeneral(GeneralStruct $general): BulkGeneratorStruct
    {
        $this->general = $general;

        return $this;
    }
}
