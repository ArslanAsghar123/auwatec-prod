<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\RichSnippets\LocalBusiness;

use DreiscSeoPro\Core\CustomSetting\Struct\AbstractCustomSettingStruct;

class AddressStruct extends AbstractCustomSettingStruct
{
    /**
     * @var string|null
     */
    protected $streetAddress;

    /**
     * @var string|null
     */
    protected $addressLocality;

    /**
     * @var string|null
     */
    protected $postalCode;

    /**
     * @var string|null
     */
    protected $addressCountry;

    /**
     * @param string|null $streetAddress
     * @param string|null $addressLocality
     * @param string|null $postalCode
     * @param string|null $addressCountry
     * @param string $settingContext
     */
    public function __construct(?string $streetAddress, ?string $addressLocality, ?string $postalCode, ?string $addressCountry, string $settingContext)
    {
        parent::__construct($settingContext);

        $this->streetAddress = $streetAddress;
        $this->addressLocality = $addressLocality;
        $this->postalCode = $postalCode;
        $this->addressCountry = $addressCountry;
    }

    public function getStreetAddress(): ?string
    {
        return $this->streetAddress;
    }

    public function setStreetAddress(?string $streetAddress): AddressStruct
    {
        $this->streetAddress = $streetAddress;

        return $this;
    }

    public function getAddressLocality(): ?string
    {
        return $this->addressLocality;
    }

    public function setAddressLocality(?string $addressLocality): AddressStruct
    {
        $this->addressLocality = $addressLocality;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): AddressStruct
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getAddressCountry(): ?string
    {
        return $this->addressCountry;
    }

    public function setAddressCountry(?string $addressCountry): AddressStruct
    {
        $this->addressCountry = $addressCountry;

        return $this;
    }
}
