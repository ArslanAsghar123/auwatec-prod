<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Content\DreiscSeoBulk\Aggregate\DreiscSeoBulkTemplate;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use DreiscSeoPro\Core\Content\DreiscSeoBulk\DreiscSeoBulkCollection;

class DreiscSeoBulkTemplateEntity extends Entity
{
    use EntityIdTrait;

    final const ID__STORAGE_NAME = 'id';
    final const ID__PROPERTY_NAME = 'id';
    final const AREA__STORAGE_NAME = 'area';
    final const AREA__PROPERTY_NAME = 'area';
    final const SEO_OPTION__STORAGE_NAME = 'seo_option';
    final const SEO_OPTION__PROPERTY_NAME = 'seoOption';
    final const NAME__STORAGE_NAME = 'name';
    final const NAME__PROPERTY_NAME = 'name';
    final const SPACELESS__STORAGE_NAME = 'spaceless';
    final const SPACELESS__PROPERTY_NAME = 'spaceless';
    final const TEMPLATE__STORAGE_NAME = 'template';
    final const TEMPLATE__PROPERTY_NAME = 'template';
    final const DREISC_SEO_BULKS__STORAGE_NAME = 'dreisc_seo_bulks';
    final const DREISC_SEO_BULKS__PROPERTY_NAME = 'dreiscSeoBulks';

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
    protected $name;

    /**
     * @var bool|null
     */
    protected $spaceless;

    /**
     * @var bool|null
     */
    protected $aiPrompt;

    /**
     * @var string|null
     */
    protected $template;

    /**
     * @var DreiscSeoBulkCollection|null
     */
    protected $dreiscSeoBulks;

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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSpaceless(): ?bool
    {
        return $this->spaceless;
    }

    public function setSpaceless(?bool $spaceless): self
    {
        $this->spaceless = $spaceless;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getAiPrompt(): ?bool
    {
        return $this->aiPrompt;
    }

    /**
     * @param bool|null $aiPrompt
     * @return DreiscSeoBulkTemplateEntity
     */
    public function setAiPrompt(?bool $aiPrompt): DreiscSeoBulkTemplateEntity
    {
        $this->aiPrompt = $aiPrompt;
        return $this;
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function setTemplate(?string $template): self
    {
        $this->template = $template;

        return $this;
    }

    /**
    * @return DreiscSeoBulkCollection|null
    */
    public function getDreiscSeoBulks(): ?DreiscSeoBulkCollection
    {
        return $this->dreiscSeoBulks;
    }

    /**
     * @param DreiscSeoBulkCollection|null $dreiscSeoBulks
     */
    public function setDreiscSeoBulks(?DreiscSeoBulkCollection $dreiscSeoBulks): self
    {
        $this->dreiscSeoBulks = $dreiscSeoBulks;

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
