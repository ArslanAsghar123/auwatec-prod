<?php declare(strict_types=1);

namespace Acris\Gpsr\Custom\Aggregate\ManufacturerDownloadTranslation;

use Acris\Gpsr\Custom\ManufacturerDownloadEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use Shopware\Core\Framework\DataAbstractionLayer\TranslationEntity;

class ManufacturerDownloadTranslationEntity extends TranslationEntity
{
    use EntityCustomFieldsTrait;

    protected ?string $fileName = null;
    protected ?string $acrisMfDId = null;
    protected ?ManufacturerDownloadEntity $acrisMfD = null;

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(?string $fileName): void
    {
        $this->fileName = $fileName;
    }

    public function getAcrisMfDId(): ?string
    {
        return $this->acrisMfDId;
    }

    public function setAcrisMfDId(?string $acrisMfDId): void
    {
        $this->acrisMfDId = $acrisMfDId;
    }

    public function getAcrisMfD(): ?ManufacturerDownloadEntity
    {
        return $this->acrisMfD;
    }

    public function setAcrisMfD(?ManufacturerDownloadEntity $acrisMfD): void
    {
        $this->acrisMfD = $acrisMfD;
    }
}
