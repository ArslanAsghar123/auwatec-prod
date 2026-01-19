<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\SocialMedia;

use DreiscSeoPro\Core\CustomSetting\Struct\CustomSettingStruct;
use DreiscSeoPro\Core\Foundation\Struct\DefaultStruct;
use RuntimeException;
use Shopware\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Content\LandingPage\LandingPageDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class SocialMediaFetcherStruct extends DefaultStruct
{
    final const POSSIBLE_ENTITY_NAMES = [
        ProductDefinition::ENTITY_NAME,
        CategoryDefinition::ENTITY_NAME,
        LandingPageDefinition::ENTITY_NAME
    ];

    /**
     * @var CustomSettingStruct
     */
    protected $customSetting;

    /**
     * @var string
     */
    protected $entityName;

    /**
     * @var string
     */
    protected $entityId;

    /**
     * @var string
     */
    protected $languageId;

    /**
     * @var SalesChannelContext|null
     */
    protected $salesChannelContext;

    /**
     * @var Entity|null
     */
    protected $entity;

    public function __construct(
        CustomSettingStruct $customSetting,
        string $entityName,
        string $entityId,
        string $languageId,
        SalesChannelContext $salesChannelContext = null,
        Entity $entity = null
    )
    {
        $this->customSetting = $customSetting;
        $this->entityId = $entityId;
        $this->languageId = $languageId;
        $this->salesChannelContext = $salesChannelContext;
        $this->entity = $entity;

        $this->setEntityName($entityName);
    }

    public function getCustomSetting(): CustomSettingStruct
    {
        return $this->customSetting;
    }

    public function setCustomSetting(CustomSettingStruct $customSetting): SocialMediaFetcherStruct
    {
        $this->customSetting = $customSetting;
        return $this;
    }

    public function getEntityName(): string
    {
        return $this->entityName;
    }

    public function setEntityName(string $entityName): SocialMediaFetcherStruct
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

    public function setEntityId(string $entityId): SocialMediaFetcherStruct
    {
        $this->entityId = $entityId;
        return $this;
    }

    public function getLanguageId(): string
    {
        return $this->languageId;
    }

    public function setLanguageId(string $languageId): SocialMediaFetcherStruct
    {
        $this->languageId = $languageId;

        return $this;
    }

    public function getEntity(): ?Entity
    {
        return $this->entity;
    }

    public function setEntity(?Entity $entity): SocialMediaFetcherStruct
    {
        $this->entity = $entity;
        return $this;
    }

    public function getSalesChannelContext(): ?SalesChannelContext
    {
        return $this->salesChannelContext;
    }

    public function setSalesChannelContext(?SalesChannelContext $salesChannelContext): SocialMediaFetcherStruct
    {
        $this->salesChannelContext = $salesChannelContext;
        return $this;
    }
}
