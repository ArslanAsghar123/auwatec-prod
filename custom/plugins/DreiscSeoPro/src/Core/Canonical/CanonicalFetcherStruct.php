<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Canonical;

use DreiscSeoPro\Core\CustomSetting\Struct\CustomSettingStruct;
use DreiscSeoPro\Core\Foundation\Struct\DefaultStruct;
use RuntimeException;
use Shopware\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Content\LandingPage\LandingPageDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;

class CanonicalFetcherStruct extends DefaultStruct
{
    final const POSSIBLE_ENTITY_NAMES = [
        ProductDefinition::ENTITY_NAME,
        CategoryDefinition::ENTITY_NAME,
        LandingPageDefinition::ENTITY_NAME,
    ];

    /**
     * @var string
     */
    protected $entityName;

    /**
     * @var string
     */
    protected $entityId;

    /**
     * @var Entity|null
     */
    protected $entity;

    /**
     * @var string
     */
    protected $languageId;

    /**
     * @var string
     */
    protected $salesChannelId;

    /**
     * @var string
     */
    protected $salesChannelDomainId;

    public function __construct(string $entityName, string $entityId, ?Entity $entity, string $languageId, string $salesChannelId, string $salesChannelDomainId)
    {
        $this->entityId = $entityId;
        $this->entity = $entity;
        $this->languageId = $languageId;
        $this->salesChannelId = $salesChannelId;
        $this->salesChannelDomainId = $salesChannelDomainId;

        $this->setEntityName($entityName);
    }

    public function getEntityName(): string
    {
        return $this->entityName;
    }

    public function setEntityName(string $entityName): CanonicalFetcherStruct
    {
        if (!in_array($entityName, self::POSSIBLE_ENTITY_NAMES, true)) {
            throw new RuntimeException('Invalid entity name: ' . $entityName);
        }

        $this->entityName = $entityName;

        return $this;
    }

    public function getEntityId(): string
    {
        return $this->entityId;
    }

    public function setEntityId(string $entityId): CanonicalFetcherStruct
    {
        $this->entityId = $entityId;

        return $this;
    }

    /**
     * @return Entity|null
     */
    public function getEntity(): ?Entity
    {
        return $this->entity;
    }

    /**
     * @param Entity|null $entity
     * @return CanonicalFetcherStruct
     */
    public function setEntity(?Entity $entity): CanonicalFetcherStruct
    {
        $this->entity = $entity;
        return $this;
    }

    public function getLanguageId(): string
    {
        return $this->languageId;
    }

    public function setLanguageId(string $languageId): CanonicalFetcherStruct
    {
        $this->languageId = $languageId;

        return $this;
    }

    public function getSalesChannelId(): string
    {
        return $this->salesChannelId;
    }

    public function setSalesChannelId(string $salesChannelId): CanonicalFetcherStruct
    {
        $this->salesChannelId = $salesChannelId;

        return $this;
    }

    public function getSalesChannelDomainId(): string
    {
        return $this->salesChannelDomainId;
    }

    public function setSalesChannelDomainId(string $salesChannelDomainId): CanonicalFetcherStruct
    {
        $this->salesChannelDomainId = $salesChannelDomainId;

        return $this;
    }
}
