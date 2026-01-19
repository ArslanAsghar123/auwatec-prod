<?php declare(strict_types=1);

namespace Acris\Gpsr\Custom\Aggregate\GpsrContactTranslation;

use Acris\Gpsr\Custom\GpsrContactEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use Shopware\Core\Framework\DataAbstractionLayer\TranslationEntity;

class GpsrContactTranslationEntity extends TranslationEntity
{
    use EntityCustomFieldsTrait;

    protected ?string $internalName = null;
    protected ?string $internalNotice = null;
    protected ?string $headline = null;
    protected ?string $text = null;
    protected ?string $modalInfoText = null;
    protected ?string $modalLinkText = null;

    protected ?string $name = null;
    protected ?string $street = null;
    protected ?string $houseNumber = null;
    protected ?string $zipcode = null;
    protected ?string $city = null;
    protected ?string $country = null;
    protected ?string $phoneNumber = null;
    protected ?string $address = null;
    protected ?string $gpsrContactId = null;
    protected ?GpsrContactEntity $gpsrContact = null;

    public function getInternalName(): ?string
    {
        return $this->internalName;
    }

    public function setInternalName(?string $internalName): void
    {
        $this->internalName = $internalName;
    }

    public function getInternalNotice(): ?string
    {
        return $this->internalNotice;
    }

    public function setInternalNotice(?string $internalNotice): void
    {
        $this->internalNotice = $internalNotice;
    }

    public function getHeadline(): ?string
    {
        return $this->headline;
    }

    public function setHeadline(?string $headline): void
    {
        $this->headline = $headline;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): void
    {
        $this->text = $text;
    }

    public function getModalInfoText(): ?string
    {
        return $this->modalInfoText;
    }

    public function setModalInfoText(?string $modalInfoText): void
    {
        $this->modalInfoText = $modalInfoText;
    }

    public function getModalLinkText(): ?string
    {
        return $this->modalLinkText;
    }

    public function setModalLinkText(?string $modalLinkText): void
    {
        $this->modalLinkText = $modalLinkText;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): void
    {
        $this->street = $street;
    }

    public function getHouseNumber(): ?string
    {
        return $this->houseNumber;
    }

    public function setHouseNumber(?string $houseNumber): void
    {
        $this->houseNumber = $houseNumber;
    }

    public function getZipcode(): ?string
    {
        return $this->zipcode;
    }

    public function setZipcode(?string $zipcode): void
    {
        $this->zipcode = $zipcode;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): void
    {
        $this->country = $country;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): void
    {
        $this->address = $address;
    }

    public function getGpsrContactId(): ?string
    {
        return $this->gpsrContactId;
    }

    public function setGpsrContactId(?string $gpsrContactId): void
    {
        $this->gpsrContactId = $gpsrContactId;
    }

    public function getGpsrContact(): ?GpsrContactEntity
    {
        return $this->gpsrContact;
    }

    public function setGpsrContact(?GpsrContactEntity $gpsrContact): void
    {
        $this->gpsrContact = $gpsrContact;
    }


}
