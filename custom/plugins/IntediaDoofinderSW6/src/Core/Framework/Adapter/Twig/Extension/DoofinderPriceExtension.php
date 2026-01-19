<?php declare(strict_types=1);

namespace Intedia\Doofinder\Core\Framework\Adapter\Twig\Extension;

use Intedia\Otto\Core\Entities\Category\CategoryEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Content\Rule\Aggregate\RuleCondition\RuleConditionEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class DoofinderPriceExtension extends AbstractExtension
{
    private EntityRepository $productRepository;
    private SalesChannelRepository $salesChannelProductRepository;

    public function __construct(
        EntityRepository       $productRepository,
        SalesChannelRepository $salesChannelProductRepository
    )
    {
        $this->productRepository             = $productRepository;
        $this->salesChannelProductRepository = $salesChannelProductRepository;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('doofinderCustomerGroupPrice', [$this, 'doofinderCustomerGroupPrice']),
            new TwigFunction('doofinderProductPriceRange', [$this, 'doofinderProductPriceRange']),
            new TwigFunction('doofinderUnitPrice', [$this, 'doofinderUnitPrice']),
            new TwigFunction('doofinderUnitName', [$this, 'doofinderUnitName']),
            new TwigFunction('doofinderUnitAmount', [$this, 'doofinderUnitAmount'])
        ];
    }

    public function doofinderCustomerGroupPrice(ProductEntity $product, $customerGroup, $customerGroups, SalesChannelContext $context)
    {
        if (!$product->getPrices()) {
            $product = $this->getSalesChannelProductById($product->getId(), $context);
        } elseif ($product->getParentId()) {
            $product = $this->getSalesChannelProductById($product->getParentId(), $context);
        } else {
            return null;
        }

        if ($product->getPrices()) {
            foreach ($product->getPrices() as $price) {
                /** @var RuleConditionEntity $condition */
                foreach ($price->getRule()->getConditions() as $condition) {
                    if ($condition->getType() == 'customerCustomerGroup') {
                        foreach ($condition->getValue()['customerGroupIds'] as $conditionCustomerGroupId) {
                            if ($conditionCustomerGroupId == $customerGroups[$customerGroup]) {
                                return $price->getPrice()->first()->getGross();
                            }
                        }
                    }
                }
            }
        }
        return null;
    }

    /**
     * @param string $productId
     * @param SalesChannelContext $context
     * @return SalesChannelProductEntity|null
     */
    protected function getSalesChannelProductById(string $productId, SalesChannelContext $context): ?SalesChannelProductEntity
    {
        $criteria = new Criteria([ $productId ]);
        $criteria->addAssociation('prices.rule');
        $criteria->addAssociation('prices.rule.conditions');
        $criteria->addAssociation('prices.conditions');
        $criteria->addAssociation('conditions');
        return $this->salesChannelProductRepository->search($criteria, $context)->first();
    }

    public function doofinderProductPriceRange(SalesChannelProductEntity $product, Context $context)
    {
        if ($product->getParentId()) {
            $product = $this->getProductById($product->getParentId(), $context);
        }

        return $this->calculate($product);
    }

    public function doofinderUnitName(SalesChannelProductEntity $product, Context $context)
    {
        if ($product->getParentId()) {
            $product = $this->getProductById($product->getParentId(), $context);
        }
        if ($product->getPackUnit()) {
            return $product->getPackUnit();
        }
        return '';
    }

    public function doofinderUnitAmount(SalesChannelProductEntity $product, Context $context)
    {
        if ($product->getParentId()) {
            $product = $this->getProductById($product->getParentId(), $context);
        }
        if ($product->getReferenceUnit()) {
            return $product->getReferenceUnit();
        }
        return '';
    }

    public function doofinderUnitPrice(SalesChannelProductEntity $product, Context $context, $gross = true)
    {
        if ($product->getParentId()) {
            $product = $this->getProductById($product->getParentId(), $context);
        }
        if ($product->getPurchaseUnit()) {
            $reference = $product->getReferenceUnit(); // 1
            $purchaseUnit = $product->getPurchaseUnit(); // 0.5
            if ($gross) {
                $price = $product->getCheapestPrice()->getPrice()->first()->getGross();
            } else {
                $price = $product->getCheapestPrice()->getPrice()->first()->getNet();
            }
            $amountInUnit = floor($reference / $purchaseUnit);
            return $price * $amountInUnit;
        }
        return '';
    }

    protected function getProductById(string $productId, Context $context): ?ProductEntity
    {
        $criteria = new Criteria([ $productId ]);
        $criteria->addAssociation('children');
        return $this->productRepository->search($criteria, $context)->first();
    }

    public function calculate(ProductEntity $product, $gross = true)
    {
        if ($gross) {
            $lowestPrice = $product->getCheapestPrice()->getPrice()->first()->getGross();
        } else {
            $lowestPrice = $product->getCheapestPrice()->getPrice()->first()->getNet();
        }
        $prices = $product->getChildren();

        if (!$prices) {
            return null;
        }

        $highestPrice = 0.00;
        foreach ($prices as $price) {
            $price->getPrice()->first()->getRegulationPrice()->getNet();
            if ($gross) {
                $productPrice = $price->getPrice()->first()->getGross();
            } else {
                $productPrice = $price->getPrice()->first()->getNet();
            }
            if ($productPrice >= $highestPrice) {
                $highestPrice = $productPrice;
            }
        }

        if ($lowestPrice !== $highestPrice) {
            return $lowestPrice . ' - ' . $highestPrice;
        }
        return null;
    }
}
