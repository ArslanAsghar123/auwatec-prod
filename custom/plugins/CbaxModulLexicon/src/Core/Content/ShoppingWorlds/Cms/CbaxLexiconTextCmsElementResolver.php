<?php declare(strict_types=1);

namespace Cbax\ModulLexicon\Core\Content\ShoppingWorlds\Cms;

use Symfony\Component\HttpFoundation\RequestStack;
use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Content\Cms\DataResolver\CriteriaCollection;
use Shopware\Core\Content\Cms\DataResolver\Element\AbstractCmsElementResolver;
use Shopware\Core\Content\Cms\DataResolver\Element\ElementDataCollection;
use Shopware\Core\Content\Cms\DataResolver\ResolverContext\ResolverContext;
use Shopware\Core\PlatformRequest;
use Shopware\Core\Content\Cms\SalesChannel\Struct\TextStruct;
use Shopware\Storefront\Framework\Routing\RequestTransformer;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Core\Content\Cms\DataResolver\ResolverContext\EntityResolverContext;

use Cbax\ModulLexicon\Bootstrap\Database;
use Cbax\ModulLexicon\Components\LexiconReplacer;

class CbaxLexiconTextCmsElementResolver extends AbstractCmsElementResolver
{
    public function __construct(
        private readonly LexiconReplacer $lexiconReplacer,
        private readonly RequestStack $requestStack,
        private readonly SystemConfigService $systemConfigService
    ) {

    }

    public function getType(): string
    {
        return 'cbax-lexicon-text';
    }

    public function collect(CmsSlotEntity $slot, ResolverContext $resolverContext): ?CriteriaCollection
    {
        return null;
    }

    public function enrich(CmsSlotEntity $slot, ResolverContext $resolverContext, ElementDataCollection $result): void
    {
        $request = $this->requestStack->getCurrentRequest();
        if (empty($request)) {
            return;
        }

        $shopUrl = $request->attributes->get(RequestTransformer::STOREFRONT_URL);

        $salesChannelContext = $request->attributes->get(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_CONTEXT_OBJECT);
        $salesChannelId    = $salesChannelContext->getSalesChannelId();
        $pluginConfig = (array)$this->systemConfigService->get(Database::CONFIG_PATH, $salesChannelId);

        $text = new TextStruct();

        $config = $slot->getFieldConfig()->get('content');
        if (empty($config)) {
            return;
        }

        if ($config->isMapped() && $resolverContext instanceof EntityResolverContext && is_string($config->getValue())) {

            $content          = (string) $this->resolveEntityValue($resolverContext->getEntity(), $config->getValue());
            $contentWithLinks = $this->lexiconReplacer->getReplaceText($content, $salesChannelId, $shopUrl, $salesChannelContext->getContext(), $pluginConfig);

            $text->setContent((string) $contentWithLinks);
        }

        if ($config->isStatic()) {

            $content          = (string) $config->getValue();
            $contentWithLinks = $this->lexiconReplacer->getReplaceText($content, $salesChannelId, $shopUrl, $salesChannelContext->getContext(), $pluginConfig);

            $text->setContent((string) $contentWithLinks);
        }

        $slot->setData($text);
    }

}
