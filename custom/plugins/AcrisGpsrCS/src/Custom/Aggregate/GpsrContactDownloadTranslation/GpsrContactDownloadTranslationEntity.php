<?php declare(strict_types=1);

namespace Acris\Gpsr\Custom\Aggregate\GpsrContactDownloadTranslation;

use Acris\Gpsr\Custom\GpsrContactDownloadEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use Shopware\Core\Framework\DataAbstractionLayer\TranslationEntity;

class GpsrContactDownloadTranslationEntity extends TranslationEntity
{
    use EntityCustomFieldsTrait;

    protected ?string $fileName = null;
    protected ?string $acrisGpsrCDId = null;
    protected ?GpsrContactDownloadEntity $acrisGpsrCD = null;

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(?string $fileName): void
    {
        $this->fileName = $fileName;
    }

    public function getAcrisGpsrCDId(): ?string
    {
        return $this->acrisGpsrCDId;
    }

    public function setAcrisGpsrCDId(?string $acrisGpsrCDId): void
    {
        $this->acrisGpsrCDId = $acrisGpsrCDId;
    }

    public function getAcrisGpsrCD(): ?GpsrContactDownloadEntity
    {
        return $this->acrisGpsrCD;
    }

    public function setAcrisGpsrCD(?GpsrContactDownloadEntity $acrisGpsrCD): void
    {
        $this->acrisGpsrCD = $acrisGpsrCD;
    }
}
