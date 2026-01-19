<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\RichSnippets;

use DreiscSeoPro\Core\CustomSetting\Struct\AbstractCustomSettingStruct;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\RichSnippets\LocalBusiness\AddressStruct;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\RichSnippets\LocalBusiness\GeneralStruct as LocalBusinessGeneralStruct;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\RichSnippets\LocalBusiness\OpeningHoursSpecificationStruct;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\RichSnippets\LocalBusiness\OpeningHoursSpecification\SpecificationStruct;

class LocalBusinessStruct extends AbstractCustomSettingStruct
{
    final const DEFAULT__GENERAL__ACTIVE = false;

    /**
     * @var LocalBusinessGeneralStruct
     */
    protected $general;

    /**
     * @var AddressStruct
     */
    protected $address;

    /**
     * @var OpeningHoursSpecificationStruct
     */
    protected $openingHoursSpecification;

    /**
     * @param array $localBusinessSettings
     * @param string $settingContext
     */
    public function __construct(array $localBusinessSettings, string $settingContext)
    {
        parent::__construct($settingContext);

        $this->general = new LocalBusinessGeneralStruct(
            $localBusinessSettings['general']['active'] ?? $this->setDefault(self::DEFAULT__GENERAL__ACTIVE),
            !empty($localBusinessSettings['general']['name']) ? $localBusinessSettings['general']['name'] : $this->setDefault(''),
            !empty($localBusinessSettings['general']['url']) ? $localBusinessSettings['general']['url'] : $this->setDefault(''),
            !empty($localBusinessSettings['general']['telephone']) ? $localBusinessSettings['general']['telephone'] : $this->setDefault(''),
            $settingContext
        );

        $this->address = new AddressStruct(
            !empty($localBusinessSettings['address']['streetAddress']) ? $localBusinessSettings['address']['streetAddress'] : $this->setDefault(''),
            !empty($localBusinessSettings['address']['addressLocality']) ? $localBusinessSettings['address']['addressLocality'] : $this->setDefault(''),
            !empty($localBusinessSettings['address']['postalCode']) ? $localBusinessSettings['address']['postalCode'] : $this->setDefault(''),
            !empty($localBusinessSettings['address']['addressCountry']) ? $localBusinessSettings['address']['addressCountry'] : $this->setDefault(''),
            $settingContext

        );

        $this->openingHoursSpecification = new OpeningHoursSpecificationStruct(
            !empty($localBusinessSettings['openingHoursSpecification']) ? $localBusinessSettings['openingHoursSpecification'] : [],
            $settingContext
        );
    }

    public function getGeneral(): LocalBusinessGeneralStruct
    {
        return $this->general;
    }

    public function setGeneral(LocalBusinessGeneralStruct $general): LocalBusinessStruct
    {
        $this->general = $general;

        return $this;
    }

    public function getAddress(): AddressStruct
    {
        return $this->address;
    }

    public function setAddress(AddressStruct $address): LocalBusinessStruct
    {
        $this->address = $address;

        return $this;
    }

    public function getOpeningHoursSpecification(): OpeningHoursSpecificationStruct
    {
        return $this->openingHoursSpecification;
    }

    public function setOpeningHoursSpecification(OpeningHoursSpecificationStruct $openingHoursSpecification): LocalBusinessStruct
    {
        $this->openingHoursSpecification = $openingHoursSpecification;

        return $this;
    }
}
