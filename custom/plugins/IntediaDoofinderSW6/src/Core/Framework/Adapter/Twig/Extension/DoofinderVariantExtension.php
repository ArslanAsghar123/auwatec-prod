<?php declare(strict_types=1);

namespace Intedia\Doofinder\Core\Framework\Adapter\Twig\Extension;

use Intedia\Otto\Core\Entities\Category\CategoryEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class DoofinderVariantExtension extends AbstractExtension
{
    public function __construct()
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('doofinderVariantInformation', [$this, 'doofinderVariantInformation'])
        ];
    }

    public function doofinderVariantInformation(ProductEntity $product, SalesChannelContext $context)
    {
        if ($product->getChildCount() >= 1) {
            foreach ($product->getChildren() as $child) {
                $options = array_map(function ($n) {
                    return $n->getGroup()->getName();
                }, $child->getOptions()->getElements());
            }

            $return = [];
            foreach ($options as $option) {
                $return[] = $option;
            }
            return $return;
        }
        $options = array_map(function ($n) {
            return [$n->getGroup()->getName() => $n->get('translated')['name']];
        }, $product->getOptions()->getElements());

        $return = [];
        foreach ($options as $option) {
            $return[] = $option;
        }
        return $return;
    }
}
