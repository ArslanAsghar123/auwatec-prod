<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\BulkGenerator;

use DreiscSeoPro\Core\CustomSetting\Struct\AbstractCustomSettingStruct;

class GeneralStruct extends AbstractCustomSettingStruct
{
    /**
     * @var bool|null
     */
    protected $startGeneratorInTheStorageProcess;

    /**
     * @param bool|null $startGeneratorInTheStorageProcess
     * @param string $settingContext
     */
    public function __construct(?bool $startGeneratorInTheStorageProcess, string $settingContext)
    {
        parent::__construct($settingContext);

        $this->startGeneratorInTheStorageProcess = $startGeneratorInTheStorageProcess;
    }

    public function isStartGeneratorInTheStorageProcess(): ?bool
    {
        return $this->startGeneratorInTheStorageProcess;
    }

    public function setStartGeneratorInTheStorageProcess(?bool $startGeneratorInTheStorageProcess): GeneralStruct
    {
        $this->startGeneratorInTheStorageProcess = $startGeneratorInTheStorageProcess;
        return $this;
    }
}
