<?php declare(strict_types=1);

namespace Acris\Gpsr\Custom\Aggregate\GpsrProductDownloadTranslation;

use Acris\Gpsr\Custom\ProductGpsrDownloadEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use Shopware\Core\Framework\DataAbstractionLayer\TranslationEntity;

class GpsrProductDownloadTranslationEntity extends TranslationEntity
{
    use EntityCustomFieldsTrait;

    protected ?string $fileName = null;
    protected ?string $acrisGprsPDId = null;
    protected ?ProductGpsrDownloadEntity $acrisGprsPD = null;

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(?string $fileName): void
    {
        $this->fileName = $fileName;
    }

    public function getAcrisGprsPDId(): ?string
    {
        return $this->acrisGprsPDId;
    }

    public function setAcrisGprsPDId(?string $acrisGprsPDId): void
    {
        $this->acrisGprsPDId = $acrisGprsPDId;
    }

    public function getAcrisGprsPD(): ?ProductGpsrDownloadEntity
    {
        return $this->acrisGprsPD;
    }

    public function setAcrisGprsPD(?ProductGpsrDownloadEntity $acrisGprsPD): void
    {
        $this->acrisGprsPD = $acrisGprsPD;
    }
}
