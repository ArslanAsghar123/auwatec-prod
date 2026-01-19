<?php declare(strict_types=1);

namespace Intedia\Doofinder\Core\Framework\Adapter\Twig\Extension;

use Shopware\Core\Content\Product\ProductEntity;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class DoofinderGroupIdExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('doofinderGroupId', [$this, 'doofinderGroupId']),
        ];
    }

    public function doofinderGroupId(ProductEntity $product, ?array $groupIds): string
    {
        if (is_null($groupIds)) {
            $groupIds = [];
        }

        if ($product->getParentId() && array_key_exists($product->getParentId(), $groupIds)) {
            return $groupIds[$product->getParentId()];
        }
        return array_key_exists($product->getId(), $groupIds) ? $groupIds[$product->getId()] : $product->getProductNumber();
    }
}
