<?php declare(strict_types=1);

namespace Cogi\CogiFooterKit\Core\Framework\Adapter\Twig\Extension;

use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FooterKitTwigExtensions extends AbstractExtension
{
    public function __construct(){}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('checkFooterStorefrontId', [$this, 'checkSalesChannelId']),
        ];
    }

    public function checkSalesChannelId(SalesChannelContext $context, String $footerKitSalesChannelId): bool
    {
        $return = false;

        if($context->getSalesChannelId() === $footerKitSalesChannelId){
            $return = true;
        }

        return $return;
    }


}
