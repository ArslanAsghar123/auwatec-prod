<?php declare(strict_types=1);

namespace Acris\Gpsr\Custom\Aggregate\GpsrNoteDownloadTranslation;

use Acris\Gpsr\Custom\GpsrNoteDownloadEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use Shopware\Core\Framework\DataAbstractionLayer\TranslationEntity;

class GpsrNoteDownloadTranslationEntity extends TranslationEntity
{
    use EntityCustomFieldsTrait;

    protected ?string $fileName = null;
    protected ?string $acrisGpsrNDId = null;
    protected ?GpsrNoteDownloadEntity $acrisGpsrND = null;

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(?string $fileName): void
    {
        $this->fileName = $fileName;
    }

    public function getAcrisGpsrNDId(): ?string
    {
        return $this->acrisGpsrNDId;
    }

    public function setAcrisGpsrNDId(?string $acrisGpsrNDId): void
    {
        $this->acrisGpsrNDId = $acrisGpsrNDId;
    }

    public function getAcrisGpsrND(): ?GpsrNoteDownloadEntity
    {
        return $this->acrisGpsrND;
    }

    public function setAcrisGpsrND(?GpsrNoteDownloadEntity $acrisGpsrND): void
    {
        $this->acrisGpsrND = $acrisGpsrND;
    }
}
