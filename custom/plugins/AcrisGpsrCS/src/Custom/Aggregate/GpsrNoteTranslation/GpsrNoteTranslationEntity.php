<?php declare(strict_types=1);

namespace Acris\Gpsr\Custom\Aggregate\GpsrNoteTranslation;

use Acris\Gpsr\Custom\GpsrNoteEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use Shopware\Core\Framework\DataAbstractionLayer\TranslationEntity;

class GpsrNoteTranslationEntity extends TranslationEntity
{
    use EntityCustomFieldsTrait;

    protected ?string $internalName = null;
    protected ?string $internalNotice = null;
    protected ?string $headline = null;
    protected ?string $text = null;
    protected ?string $modalInfoText = null;
    protected ?string $modalLinkText = null;
    protected ?string $gpsrNoteId = null;
    protected ?GpsrNoteEntity $gpsrNote = null;

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

    public function getGpsrNoteId(): ?string
    {
        return $this->gpsrNoteId;
    }

    public function setGpsrNoteId(?string $gpsrNoteId): void
    {
        $this->gpsrNoteId = $gpsrNoteId;
    }

    public function getGpsrNote(): ?GpsrNoteEntity
    {
        return $this->gpsrNote;
    }

    public function setGpsrNote(?GpsrNoteEntity $gpsrNote): void
    {
        $this->gpsrNote = $gpsrNote;
    }
}
