<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Content\DreiscSeoSetting;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;

class DreiscSeoSettingEntity extends Entity
{
    use EntityIdTrait;

    final const ID__STORAGE_NAME = 'id';
    final const ID__PROPERTY_NAME = 'id';
    final const KEY__STORAGE_NAME = 'key';
    final const KEY__PROPERTY_NAME = 'key';
    final const VALUE__STORAGE_NAME = 'value';
    final const VALUE__PROPERTY_NAME = 'value';
    final const SALES_CHANNEL_ID__STORAGE_NAME = 'sales_channel_id';
    final const SALES_CHANNEL_ID__PROPERTY_NAME = 'salesChannelId';
    final const SALES_CHANNEL__STORAGE_NAME = 'sales_channel';
    final const SALES_CHANNEL__PROPERTY_NAME = 'salesChannel';

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $key;

    /**
     * @var array
     */
    protected $value;

    /**
     * @var string|null
     */
    protected $salesChannelId;

    /**
     * @var SalesChannelEntity|null
     */
    protected $salesChannel;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function setKey(string $key): self
    {
        $this->key = $key;

        return $this;
    }

    public function getValue(): array
    {
        return $this->value;
    }

    public function setValue(array $value): self
    {
        $this->value = $value;

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

    public function getSalesChannel(): ?SalesChannelEntity
    {
        return $this->salesChannel;
    }

    public function setSalesChannel(?SalesChannelEntity $salesChannel): self
    {
        $this->salesChannel = $salesChannel;

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
