<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Foundation\CustomSettingEntity;

use DreiscSeoPro\Core\Foundation\Dal\EntityRepository;
use DreiscSeoPro\Core\Foundation\Struct\DefaultStruct;

class CustomSettingEntityStruct extends DefaultStruct
{
    /**
     * @var EntityRepository
     */
    protected $entityRepository;

    /**
     * @var string
     */
    protected $salesChannelId;

    /**
     * @var bool
     */
    protected $mergeDefaultToSalesChannel;

    /**
     * @param string|null $salesChannelId
     * @param string $keyField
     * @param string $valueField
     * @param string $salesChannelIdField
     */
    public function __construct(EntityRepository $entityRepository, string $salesChannelId = null, bool $mergeDefaultToSalesChannel = false, protected $keyField = 'key', protected $valueField = 'value', protected $salesChannelIdField = 'salesChannelId')
    {
        $this->entityRepository = $entityRepository;
        $this->salesChannelId = $salesChannelId;
        $this->mergeDefaultToSalesChannel = $mergeDefaultToSalesChannel;
    }

    public function getEntityRepository(): EntityRepository
    {
        return $this->entityRepository;
    }

    public function setEntityRepository(EntityRepository $entityRepository): CustomSettingEntityStruct
    {
        $this->entityRepository = $entityRepository;

        return $this;
    }

    /**
     * @return string
     */
    public function getSalesChannelId(): ?string
    {
        return $this->salesChannelId;
    }

    /**
     * @param string $salesChannelId
     */
    public function setSalesChannelId(?string $salesChannelId): CustomSettingEntityStruct
    {
        $this->salesChannelId = $salesChannelId;

        return $this;
    }

    public function isMergeDefaultToSalesChannel(): bool
    {
        return $this->mergeDefaultToSalesChannel;
    }

    public function setMergeDefaultToSalesChannel(bool $mergeDefaultToSalesChannel): CustomSettingEntityStruct
    {
        $this->mergeDefaultToSalesChannel = $mergeDefaultToSalesChannel;
        return $this;
    }

    public function getKeyField(): string
    {
        return $this->keyField;
    }

    public function setKeyField(string $keyField): CustomSettingEntityStruct
    {
        $this->keyField = $keyField;

        return $this;
    }

    public function getValueField(): string
    {
        return $this->valueField;
    }

    public function setValueField(string $valueField): CustomSettingEntityStruct
    {
        $this->valueField = $valueField;

        return $this;
    }

    public function getSalesChannelIdField(): string
    {
        return $this->salesChannelIdField;
    }

    public function setSalesChannelIdField(string $salesChannelIdField): CustomSettingEntityStruct
    {
        $this->salesChannelIdField = $salesChannelIdField;

        return $this;
    }
}
