<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\RichSnippets\LocalBusiness;

use DreiscSeoPro\Core\CustomSetting\Struct\AbstractCustomSettingStruct;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\RichSnippets\LocalBusiness\OpeningHoursSpecification\SpecificationStruct;

class OpeningHoursSpecificationStruct extends AbstractCustomSettingStruct
{
    final const DAYS_OF_WEEK = [
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
        'sunday'
    ];

    /**
     * @var SpecificationStruct
     */
    protected $monday;

    /**
     * @var SpecificationStruct
     */
    protected $tuesday;

    /**
     * @var SpecificationStruct
     */
    protected $wednesday;

    /**
     * @var SpecificationStruct
     */
    protected $thursday;

    /**
     * @var SpecificationStruct
     */
    protected $friday;

    /**
     * @var SpecificationStruct
     */
    protected $saturday;

    /**
     * @var SpecificationStruct
     */
    protected $sunday;

    /**
     * @param array $specifications
     * @param string $settingContext
     */
    public function __construct(array $openingHoursSpecificationSettings, string $settingContext)
    {
        parent::__construct($settingContext);

        foreach(self::DAYS_OF_WEEK as $dayOfWeek) {
            $this->{$dayOfWeek} = new SpecificationStruct(
                $openingHoursSpecificationSettings[$dayOfWeek]['active'] ?? $this->setDefault(false),
                !empty($openingHoursSpecificationSettings[$dayOfWeek]['opens']) ? $openingHoursSpecificationSettings[$dayOfWeek]['opens'] : $this->setDefault(''),
                !empty($openingHoursSpecificationSettings[$dayOfWeek]['closes']) ? $openingHoursSpecificationSettings[$dayOfWeek]['closes'] : $this->setDefault(''),
                $settingContext
            );
        }

    }

    public function getMonday(): SpecificationStruct
    {
        return $this->monday;
    }

    public function setMonday(SpecificationStruct $monday): OpeningHoursSpecificationStruct
    {
        $this->monday = $monday;

        return $this;
    }

    public function getTuesday(): SpecificationStruct
    {
        return $this->tuesday;
    }

    public function setTuesday(SpecificationStruct $tuesday): OpeningHoursSpecificationStruct
    {
        $this->tuesday = $tuesday;

        return $this;
    }

    public function getWednesday(): SpecificationStruct
    {
        return $this->wednesday;
    }

    public function setWednesday(SpecificationStruct $wednesday): OpeningHoursSpecificationStruct
    {
        $this->wednesday = $wednesday;

        return $this;
    }

    public function getThursday(): SpecificationStruct
    {
        return $this->thursday;
    }

    public function setThursday(SpecificationStruct $thursday): OpeningHoursSpecificationStruct
    {
        $this->thursday = $thursday;

        return $this;
    }

    public function getFriday(): SpecificationStruct
    {
        return $this->friday;
    }

    public function setFriday(SpecificationStruct $friday): OpeningHoursSpecificationStruct
    {
        $this->friday = $friday;

        return $this;
    }

    public function getSaturday(): SpecificationStruct
    {
        return $this->saturday;
    }

    public function setSaturday(SpecificationStruct $saturday): OpeningHoursSpecificationStruct
    {
        $this->saturday = $saturday;

        return $this;
    }

    public function getSunday(): SpecificationStruct
    {
        return $this->sunday;
    }

    public function setSunday(SpecificationStruct $sunday): OpeningHoursSpecificationStruct
    {
        $this->sunday = $sunday;

        return $this;
    }
}
