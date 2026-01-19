<?php declare(strict_types=1);

namespace DreiscSeoPro\Decorator\Core\Content\Seo;

use DreiscSeoPro\Core\CustomSetting\CustomSettingLoader;
use Shopware\Core\Content\Seo\SeoUrlPlaceholderHandlerInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class SeoUrlPlaceholderHandlerDecorator implements SeoUrlPlaceholderHandlerInterface
{
    public function __construct(private readonly SeoUrlPlaceholderHandlerInterface $decoratedSeoUrlPlaceholderHandler, private readonly CustomSettingLoader $customSettingLoader)
    {
    }


    public function generate($name, array $parameters = []): string
    {
        return $this->decoratedSeoUrlPlaceholderHandler->generate($name, $parameters);
    }

    /**
     * @param string $content
     * @param string $host
     * @param SalesChannelContext $context
     * @return string
     * @throws InconsistentCriteriaIdsException
     */
    public function replace(string $content, string $host, SalesChannelContext $context): string
    {
        /** Load the custom settings */
        $customSettings = $this->customSettingLoader->load();

        $content = $this->decoratedSeoUrlPlaceholderHandler->replace($content, $host, $context);

        /** Abort, if json ld support was not activated */
        if (false === $customSettings->getRichSnippets()->getGeneral()->isActive()) {
            return $content;
        }

        /** Remove all meta content itemtype tags */
//        $content = preg_replace('/<meta[\s]*?itemtype\s{0,}=\s{0,}"[\s\S]*?"[\s]*?content\s{0,}=\s{0,}"[\s\S]*?"[\s]*?\/>/m', '', $content);

        /** Remove all meta content itemprop tags */
//        $content = preg_replace('/<meta[\s]*?itemprop\s{0,}=\s{0,}"[\s\S]*?"[\s]*?content\s{0,}=\s{0,}"[\s\S]*?"[\s]*?\/>/m', '', $content);

        /** Remove all itemtype tags */
        $content = preg_replace('/itemtype\s{0,}=\s{0,}[\"|\'][\S\s]*?[\"|\']/m', '', $content);

        /** Remove all itemprop tags */
        $content = preg_replace('/itemprop\s{0,}=\s{0,}[\"|\'][\S\s]*?[\"|\']/m', '', $content);

        return $content;
    }
}
