<?php declare(strict_types=1);

namespace Acris\Gpsr\Custom\Aggregate\GpsrManufacturerDownloadTranslation;

use Acris\Gpsr\Custom\GpsrManufacturerDownloadEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use Shopware\Core\Framework\DataAbstractionLayer\TranslationEntity;

class GpsrManufacturerDownloadTranslationEntity extends TranslationEntity
{
    use EntityCustomFieldsTrait;

    protected ?string $fileName = null;
    protected ?string $acrisGpsrMfDId = null;
    protected ?GpsrManufacturerDownloadEntity $acrisGpsrMfD = null;

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(?string $fileName): void
    {
        $this->fileName = $fileName;
    }

    public function getAcrisGpsrMfDId(): ?string
    {
        return $this->acrisGpsrMfDId;
    }

    public function setAcrisGpsrMfDId(?string $acrisGpsrMfDId): void
    {
        $this->acrisGpsrMfDId = $acrisGpsrMfDId;
    }

    public function getAcrisGpsrMfD(): ?GpsrManufacturerDownloadEntity
    {
        return $this->acrisGpsrMfD;
    }

    public function setAcrisGpsrMfD(?GpsrManufacturerDownloadEntity $acrisGpsrMfD): void
    {
        $this->acrisGpsrMfD = $acrisGpsrMfD;
    }
}
