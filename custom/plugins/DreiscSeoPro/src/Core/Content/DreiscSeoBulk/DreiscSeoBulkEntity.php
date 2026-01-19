<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Content\DreiscSeoBulk;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\System\CustomField\CustomFieldEntity;
use Shopware\Core\System\Language\LanguageEntity;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Shopware\Core\Content\Category\CategoryEntity;
use DreiscSeoPro\Core\Content\DreiscSeoBulk\Aggregate\DreiscSeoBulkTemplate\DreiscSeoBulkTemplateEntity;

class DreiscSeoBulkEntity extends Entity
{
    use EntityIdTrait;

    final const ID__STORAGE_NAME = 'id';
    final const ID__PROPERTY_NAME = 'id';
    final const AREA__STORAGE_NAME = 'area';
    final const AREA__PROPERTY_NAME = 'area';
    final const SEO_OPTION__STORAGE_NAME = 'seo_option';
    final const SEO_OPTION__PROPERTY_NAME = 'seoOption';
    final const LANGUAGE_ID__STORAGE_NAME = 'language_id';
    final const LANGUAGE_ID__PROPERTY_NAME = 'languageId';
    final const SALES_CHANNEL_ID__STORAGE_NAME = 'sales_channel_id';
    final const SALES_CHANNEL_ID__PROPERTY_NAME = 'salesChannelId';
    final const CATEGORY_ID__STORAGE_NAME = 'category_id';
    final const CATEGORY_ID__PROPERTY_NAME = 'categoryId';
    final const DREISC_SEO_BULK_TEMPLATE_ID__STORAGE_NAME = 'dreisc_seo_bulk_template_id';
    final const DREISC_SEO_BULK_TEMPLATE_ID__PROPERTY_NAME = 'dreiscSeoBulkTemplateId';
    final const PRIORITY__STORAGE_NAME = 'priority';
    final const PRIORITY__PROPERTY_NAME = 'priority';
    final const OVERWRITE__STORAGE_NAME = 'overwrite';
    final const OVERWRITE__PROPERTY_NAME = 'overwrite';
    final const INHERIT__STORAGE_NAME = 'inherit';
    final const INHERIT__PROPERTY_NAME = 'inherit';
    final const LANGUAGE__STORAGE_NAME = 'language';
    final const LANGUAGE__PROPERTY_NAME = 'language';
    final const SALES_CHANNEL__STORAGE_NAME = 'sales_channel';
    final const SALES_CHANNEL__PROPERTY_NAME = 'salesChannel';
    final const CATEGORY__STORAGE_NAME = 'category';
    final const CATEGORY__PROPERTY_NAME = 'category';
    final const DREISC_SEO_BULK_TEMPLATE__STORAGE_NAME = 'dreisc_seo_bulk_template';
    final const DREISC_SEO_BULK_TEMPLATE__PROPERTY_NAME = 'dreiscSeoBulkTemplate';

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $area;

    /**
     * @var string
     */
    protected $seoOption;

    /**
     * @var string
     */
    protected $languageId;

    /**
     * @var string|null
     */
    protected $salesChannelId;

    /**
     * @var string
     */
    protected $categoryId;

    /**
     * @var string
     */
    protected $categoryVersionId;

    /**
     * @var string|null
     */
    protected $dreiscSeoBulkTemplateId;

    /**
     * @var int|null
     */
    protected $priority;

    /**
     * @var string|null
     */
    protected $overwrite;

    /**
     * @var string|null
     */
    protected $overwriteCustomFieldId;

    /**
     * @var CustomFieldEntity|null
     */
    protected $overwriteCustomField;

    /**
     * @var bool|null
     */
    protected $inherit;

    /**
     * @var LanguageEntity|null
     */
    protected $language;

    /**
     * @var SalesChannelEntity|null
     */
    protected $salesChannel;

    /**
     * @var CategoryEntity|null
     */
    protected $category;

    /**
     * @var DreiscSeoBulkTemplateEntity|null
     */
    protected $dreiscSeoBulkTemplate;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getArea(): string
    {
        return $this->area;
    }

    public function setArea(string $area): self
    {
        $this->area = $area;

        return $this;
    }

    public function getSeoOption(): string
    {
        return $this->seoOption;
    }

    public function setSeoOption(string $seoOption): self
    {
        $this->seoOption = $seoOption;

        return $this;
    }

    public function getLanguageId(): string
    {
        return $this->languageId;
    }

    public function setLanguageId(string $languageId): self
    {
        $this->languageId = $languageId;

        return $this;
    }

    public function getSalesChannelId(): ?string
    {
        return $this->salesChannelId;
    }

    public function setSalesChannelId(?string $salesChannelId): self
    {
        $this->salesChannelId = $salesChannelId;

        return $this;
    }

    public function getCategoryId(): string
    {
        return $this->categoryId;
    }

    public function setCategoryId(string $categoryId): self
    {
        $this->categoryId = $categoryId;

        return $this;
    }

    /**
     * @return string
     */
    public function getCategoryVersionId(): string
    {
        return $this->categoryVersionId;
    }

    /**
     * @param string $categoryVersionId
     * @return DreiscSeoBulkEntity
     */
    public function setCategoryVersionId(string $categoryVersionId): DreiscSeoBulkEntity
    {
        $this->categoryVersionId = $categoryVersionId;
        return $this;
    }

    public function getDreiscSeoBulkTemplateId(): ?string
    {
        return $this->dreiscSeoBulkTemplateId;
    }

    public function setDreiscSeoBulkTemplateId(?string $dreiscSeoBulkTemplateId): self
    {
        $this->dreiscSeoBulkTemplateId = $dreiscSeoBulkTemplateId;

        return $this;
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function setPriority(?int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getOverwrite(): ?string
    {
        return $this->overwrite;
    }

    /**
     * @param string|null $overwrite
     * @return DreiscSeoBulkEntity
     */
    public function setOverwrite(?string $overwrite): DreiscSeoBulkEntity
    {
        $this->overwrite = $overwrite;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getOverwriteCustomFieldId(): ?string
    {
        return $this->overwriteCustomFieldId;
    }

    /**
     * @param string|null $overwriteCustomFieldId
     * @return DreiscSeoBulkEntity
     */
    public function setOverwriteCustomFieldId(?string $overwriteCustomFieldId): DreiscSeoBulkEntity
    {
        $this->overwriteCustomFieldId = $overwriteCustomFieldId;
        return $this;
    }

    /**
     * @return CustomFieldEntity|null
     */
    public function getOverwriteCustomField(): ?CustomFieldEntity
    {
        return $this->overwriteCustomField;
    }

    /**
     * @param CustomFieldEntity|null $overwriteCustomField
     * @return DreiscSeoBulkEntity
     */
    public function setOverwriteCustomField(?CustomFieldEntity $overwriteCustomField): DreiscSeoBulkEntity
    {
        $this->overwriteCustomField = $overwriteCustomField;
        return $this;
    }

    public function getInherit(): ?bool
    {
        return $this->inherit;
    }

    public function setInherit(?bool $inherit): self
    {
        $this->inherit = $inherit;

        return $this;
    }

    public function getLanguage(): ?LanguageEntity
    {
        return $this->language;
    }

    public function setLanguage(?LanguageEntity $language): self
    {
        $this->language = $language;

        return $this;
    }

    public function getSalesChannel(): ?SalesChannelEntity
    {
        return $this->salesChannel;
    }

    public function setSalesChannel(?SalesChannelEntity $salesChannel): self
    {
        $this->salesChannel = $salesChannel;

        return $this;
    }

    public function getCategory(): ?CategoryEntity
    {
        return $this->category;
    }

    public function setCategory(?CategoryEntity $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getDreiscSeoBulkTemplate(): ?DreiscSeoBulkTemplateEntity
    {
        return $this->dreiscSeoBulkTemplate;
    }

    public function setDreiscSeoBulkTemplate(?DreiscSeoBulkTemplateEntity $dreiscSeoBulkTemplate): self
    {
        $this->dreiscSeoBulkTemplate = $dreiscSeoBulkTemplate;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $jsonArray = [];
        foreach (get_object_vars($this) as $key => $value) {
            $jsonArray[$key] = $value;
        }

        return $jsonArray;
    }
}
