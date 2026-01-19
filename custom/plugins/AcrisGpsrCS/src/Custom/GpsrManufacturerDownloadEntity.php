<?php declare(strict_types=1);

namespace Acris\Gpsr\Custom;

use Acris\Gpsr\Custom\Aggregate\GpsrManufacturerDownloadTranslation\GpsrManufacturerDownloadTranslationCollection;
use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\System\Language\LanguageCollection;

class GpsrManufacturerDownloadEntity extends Entity
{
    use EntityIdTrait;

    /**
     * @var LanguageCollection|null
     */
    protected $languages;

    /**
     * @var string
     */
    protected $acrisGpsrManufacturerId;

    /**
     * @var GpsrManufacturerEntity
     */
    protected $manufactuer;

    /**
     * @var int|null
     */
    protected $position;

    /**
     * @var string
     */
    protected $mediaId;

    /**
     * @var string|null
     */
    protected $previewMediaId;

    /**
     * @var array|null
     */
    protected $languageIds;

    /**
     * @var MediaEntity
     */
    protected $media;

    /**
     * @var MediaEntity|null
     */
    protected $previewMedia;

    /**
     * @var GpsrManufacturerDownloadTranslationCollection|null
     */
    protected $translations;

    /**
     * @var boolean
     */
    protected $previewImageEnabled;

    /**
     * @var string|null
     */
    protected $fileName;

    /**
     * @return LanguageCollection|null
     */
    public function getLanguages(): ?LanguageCollection
    {
        return $this->languages;
    }

    /**
     * @param LanguageCollection|null $languages
     */
    public function setLanguages(?LanguageCollection $languages): void
    {
        $this->languages = $languages;
    }

    /**
     * @return string
     */
    public function getMediaId(): string
    {
        return $this->mediaId;
    }

    /**
     * @param string $mediaId
     */
    public function setMediaId(string $mediaId): void
    {
        $this->mediaId = $mediaId;
    }

    /**
     * @return MediaEntity
     */
    public function getMedia(): MediaEntity
    {
        return $this->media;
    }

    /**
     * @param MediaEntity $media
     */
    public function setMedia(MediaEntity $media): void
    {
        $this->media = $media;
    }

    /**
     * @return GpsrManufacturerEntity
     */
    public function getManufacturer(): GpsrManufacturerEntity
    {
        return $this->manufactuer;
    }

    /**
     * @param GpsrManufacturerEntity $contact
     */
    public function setManufacturer(GpsrManufacturerEntity $manufactuer): void
    {
        $this->manufactuer = $manufactuer;
    }

    /**
     * @return string
     */
    public function getAcrisGpsrManufacturer_id(): string
    {
        return $this->acrisGpsrManufacturerId;
    }

    /**
     * @param string $contactId
     */
    public function setAcrisGpsrManufacturer_id(string $acrisGpsrManufacturerId): void
    {
        $this->acrisGpsrManufacturerId = $acrisGpsrManufacturerId;
    }

    /**
     * @return int|null
     */
    public function getPosition(): ?int
    {
        return $this->position;
    }

    /**
     * @param int|null $position
     */
    public function setPosition(?int $position): void
    {
        $this->position = $position;
    }

    /**
     * @return GpsrManufacturerDownloadTranslationCollection|null
     */
    public function getTranslations(): ?GpsrManufacturerDownloadTranslationCollection
    {
        return $this->translations;
    }

    /**
     * @param GpsrManufacturerDownloadTranslationCollection|null $translations
     */
    public function setTranslations(?GpsrManufacturerDownloadTranslationCollection $translations): void
    {
        $this->translations = $translations;
    }

    /**
     * @return array|null
     */
    public function getLanguageIds(): ?array
    {
        return $this->languageIds;
    }

    /**
     * @param array|null $languageIds
     */
    public function setLanguageIds(?array $languageIds): void
    {
        $this->languageIds = $languageIds;
    }

    /**
     * @return bool
     */
    public function isPreviewImageEnabled(): bool
    {
        return $this->previewImageEnabled;
    }

    /**
     * @param bool $previewImageEnabled
     */
    public function setPreviewImageEnabled(bool $previewImageEnabled): void
    {
        $this->previewImageEnabled = $previewImageEnabled;
    }

    /**
     * @return string|null
     */
    public function getPreviewMediaId(): ?string
    {
        return $this->previewMediaId;
    }

    /**
     * @param string|null $previewMediaId
     */
    public function setPreviewMediaId(?string $previewMediaId): void
    {
        $this->previewMediaId = $previewMediaId;
    }

    /**
     * @return MediaEntity|null
     */
    public function getPreviewMedia(): ?MediaEntity
    {
        return $this->previewMedia;
    }

    /**
     * @param MediaEntity|null $previewMedia
     */
    public function setPreviewMedia(?MediaEntity $previewMedia): void
    {
        $this->previewMedia = $previewMedia;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(?string $fileName): void
    {
        $this->fileName = $fileName;
    }
}
