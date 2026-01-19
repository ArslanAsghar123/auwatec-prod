<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Foundation\Seo\Struct;

use DreiscSeoPro\Core\Foundation\Struct\DefaultStruct;
use Shopware\Core\Framework\Struct\Struct;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainEntity;

class SeoUrlParserResultStruct extends DefaultStruct
{
    public function __construct(private ?SalesChannelDomainEntity $salesChannelDomainEntity = null, private ?string $baseUrl = null)
    {
    }

    public function getSalesChannelDomainEntity(): ?SalesChannelDomainEntity
    {
        return $this->salesChannelDomainEntity;
    }

    public function setSalesChannelDomainEntity(?SalesChannelDomainEntity $salesChannelDomainEntity): SeoUrlParserResultStruct
    {
        $this->salesChannelDomainEntity = $salesChannelDomainEntity;
        return $this;
    }

    public function getBaseUrl(): ?string
    {
        return $this->baseUrl;
    }

    public function setBaseUrl(?string $baseUrl): SeoUrlParserResultStruct
    {
        $this->baseUrl = $baseUrl;
        return $this;
    }
}
