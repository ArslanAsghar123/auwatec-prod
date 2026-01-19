<?php declare(strict_types=1);

namespace DreiscSeoPro\Test\Behaviour\Context;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextFactory;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextService;
use Shopware\Core\Test\TestDefaults;
use Symfony\Component\DependencyInjection\ContainerInterface;

trait SalesChannelContextTestBehaviour
{
    abstract protected static function getContainer(): ContainerInterface;

    protected function _createDefaultSalesChannelContext(array $options = [])
    {
        $salesChannelDomain = $this->getContainer()->get('sales_channel_domain.repository')->search(
            (new Criteria())->addFilter(
                new EqualsFilter('salesChannelId', TestDefaults::SALES_CHANNEL
            ))
        , Context::createCLIContext())->first();

        $options = array_merge([
            SalesChannelContextService::DOMAIN_ID => $salesChannelDomain->getId()
        ], $options);

        $salesChannelContext = $this->getContainer()->get(SalesChannelContextFactory::class)
            ->create(Uuid::randomHex(), TestDefaults::SALES_CHANNEL, $options);

        return $salesChannelContext;
    }

    protected function _createDeLanguageSalesChannelContext(array $options = [])
    {
        $options = array_merge([
            SalesChannelContextService::LANGUAGE_ID => $this->getDeDeLanguageId()
        ], $options);

        return $this->_createDefaultSalesChannelContext($options);
    }
}
