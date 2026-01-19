<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\RobotsTag;

use DreiscSeoPro\Core\CustomSetting\Struct\CustomSettingStruct;
use DreiscSeoPro\Core\Foundation\Struct\DefaultStruct;
use RuntimeException;
use Shopware\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Content\LandingPage\LandingPageDefinition;
use Shopware\Core\Content\Product\ProductDefinition;

class RobotsTagFetcherStruct extends DefaultStruct
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
     * @var array|null
     */
    protected $requestParams;

    public function __construct(CustomSettingStruct $customSetting, string $entityName, string $entityId, string $languageId, ?array $requestParams)
    {
        $this->customSetting = $customSetting;
        $this->entityId = $entityId;
        $this->languageId = $languageId;
        $this->requestParams = $requestParams;

        $this->setEntityName($entityName);
    }

    public function getCustomSetting(): CustomSettingStruct
    {
        return $this->customSetting;
    }

    public function setCustomSetting(CustomSettingStruct $customSetting): RobotsTagFetcherStruct
    {
        $this->customSetting = $customSetting;
        return $this;
    }

    public function getEntityName(): string
    {
        return $this->entityName;
    }

    public function setEntityName(string $entityName): RobotsTagFetcherStruct
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

    public function setEntityId(string $entityId): RobotsTagFetcherStruct
    {
        $this->entityId = $entityId;
        return $this;
    }

    public function getLanguageId(): string
    {
        return $this->languageId;
    }

    public function setLanguageId(string $languageId): RobotsTagFetcherStruct
    {
        $this->languageId = $languageId;

        return $this;
    }

    public function getRequestParams(): ?array
    {
        return $this->requestParams;
    }

    public function setRequestParams(?array $requestParams): RobotsTagFetcherStruct
    {
        $this->requestParams = $requestParams;

        return $this;
    }
}
