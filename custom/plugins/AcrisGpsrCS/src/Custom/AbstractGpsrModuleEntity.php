<?php declare(strict_types=1);

namespace Acris\Gpsr\Custom;

use Acris\Gpsr\Custom\Aggregate\GpsrContactTranslation\GpsrContactTranslationCollection;
use Shopware\Core\Content\ProductStream\ProductStreamCollection;
use Shopware\Core\Content\Rule\RuleCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\System\SalesChannel\SalesChannelCollection;

abstract class AbstractGpsrModuleEntity extends Entity
{
    use EntityIdTrait;

    protected bool $active;
    protected ?string $internalId = null;
    protected ?int $priority = null;
    protected ?string $displayType = null;
    protected ?string $tabPosition = null;
    protected ?string $descriptionDisplay = null;
    protected ?string $descriptionPosition = null;
    protected ?string $displaySeparator = null;
    protected ?ProductStreamCollection $productStreams = null;
    protected ?SalesChannelCollection $salesChannels = null;
    protected ?RuleCollection $rules = null;
    protected ?EntityCollection $translations = null;

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getInternalId(): ?string
    {
        return $this->internalId;
    }

    public function setInternalId(?string $internalId): void
    {
        $this->internalId = $internalId;
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function setPriority(?int $priority): void
    {
        $this->priority = $priority;
    }

    public function getDisplayType(): ?string
    {
        return $this->displayType;
    }

    public function setDisplayType(?string $displayType): void
    {
        $this->displayType = $displayType;
    }

    public function getTabPosition(): ?string
    {
        return $this->tabPosition;
    }

    public function setTabPosition(?string $tabPosition): void
    {
        $this->tabPosition = $tabPosition;
    }

    public function getDescriptionDisplay(): ?string
    {
        return $this->descriptionDisplay;
    }

    public function setDescriptionDisplay(?string $descriptionDisplay): void
    {
        $this->descriptionDisplay = $descriptionDisplay;
    }

    public function getDescriptionPosition(): ?string
    {
        return $this->descriptionPosition;
    }

    public function setDescriptionPosition(?string $descriptionPosition): void
    {
        $this->descriptionPosition = $descriptionPosition;
    }

    public function getDisplaySeparator(): ?string
    {
        return $this->displaySeparator;
    }

    public function setDisplaySeparator(?string $displaySeparator): void
    {
        $this->displaySeparator = $displaySeparator;
    }

    public function getProductStreams(): ?ProductStreamCollection
    {
        return $this->productStreams;
    }

    public function setProductStreams(?ProductStreamCollection $productStreams): void
    {
        $this->productStreams = $productStreams;
    }

    public function getSalesChannels(): ?SalesChannelCollection
    {
        return $this->salesChannels;
    }

    public function setSalesChannels(?SalesChannelCollection $salesChannels): void
    {
        $this->salesChannels = $salesChannels;
    }

    public function getRules(): ?RuleCollection
    {
        return $this->rules;
    }

    public function setRules(?RuleCollection $rules): void
    {
        $this->rules = $rules;
    }

    public function getTranslations(): ?EntityCollection
    {
        return $this->translations;
    }

    public function setTranslations(?EntityCollection $translations): void
    {
        $this->translations = $translations;
    }
}
