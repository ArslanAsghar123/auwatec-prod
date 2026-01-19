<?php declare(strict_types=1);

namespace DreiscSeoPro\Test\Behaviour\Entity;

use DreiscSeoPro\Test\Behaviour\Entity\ProductEntityTestBehaviour\ProductEntityTestBehaviourStruct;
use Shopware\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityDefinition;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Pricing\PriceCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Test\TestDefaults;
use Symfony\Component\DependencyInjection\ContainerInterface;

trait ProductEntityTestBehaviour
{

    /**
     * @param array $product
     * @return array
     */
    public function addVariantData(array $product): array
    {
        $product['parentId'] = $product['id'];
        return $product;
    }

    abstract protected static function getContainer(): ContainerInterface;
    protected function _createProduct(\Closure $productClosure = null): ?ProductEntity
    {
        $product = $this->getProductBase();

        if ($productClosure) {
            $productClosure($product);
        }

        $this->upsertProduct($product);

        return $this->fetchProduct($product['id']);
    }
    protected function _createVariantProduct(\Closure $productClosure = null): ?ProductEntity
    {
        $product = $this->getProductBase();
        $this->addVariantInfo($product);

        if ($productClosure) {
            $productClosure($product);
        }

        $this->upsertProduct($product);

        return $this->fetchProduct($product['id']);
    }

    private function getProductBase(): array
    {
        return [
            'id' => Uuid::randomHex(),
            'productNumber' => Uuid::randomHex(),
            'stock' => 1,
            'name' => 'Test product',
            'price' => [['currencyId' => Defaults::CURRENCY, 'gross' => 10, 'net' => 9, 'linked' => false]],
            'manufacturer' => ['id' => Uuid::randomHex(), 'name' => 'test'],
            'tax' => ['id' => Uuid::randomHex(), 'taxRate' => 17, 'name' => 'with id'],
            'visibilities' => [
                ['salesChannelId' => TestDefaults::SALES_CHANNEL, 'visibility' => ProductVisibilityDefinition::VISIBILITY_ALL],
            ]
        ];
    }
    private function fetchProduct(string $id): ?ProductEntity
    {
        $criteria = new Criteria([$id]);
        $criteria->addAssociation('translations');
        $criteria->addAssociation('children.translations');

        return $this->getContainer()->get('product.repository')
            ->search($criteria, Context::createCLIContext())
            ->first();
    }

    private function upsertProduct(array $product): void
    {
        $this->getContainer()->get('product.repository')->upsert([$product], Context::createCLIContext());
    }

    private function addVariantInfo(array &$product): void
    {
        $optionIds = [
            'red' => Uuid::randomHex(),
            'green' => Uuid::randomHex(),
            'xl' => Uuid::randomHex(),
            'l' => Uuid::randomHex(),
        ];

        $groupIds = [
            'color' => Uuid::randomHex(),
            'size' => Uuid::randomHex(),
        ];

        $product['configuratorSettings'] = [
            [
                'option' => [
                    'id' => $optionIds['red'],
                    'name' => 'Red',
                    'group' => [
                        'id' => $groupIds['color'],
                        'name' => 'Color',
                    ],
                ],
            ],
            [
                'option' => [
                    'id' => $optionIds['green'],
                    'name' => 'Green',
                    'group' => [
                        'id' => $groupIds['color'],
                        'name' => 'Color',
                    ],
                ],
            ],
            [
                'option' => [
                    'id' => $optionIds['xl'],
                    'name' => 'XL',
                    'group' => [
                        'id' => $groupIds['size'],
                        'name' => 'size',
                    ],
                ],
            ],
            [
                'option' => [
                    'id' => $optionIds['l'],
                    'name' => 'L',
                    'group' => [
                        'id' => $groupIds['size'],
                        'name' => 'size',
                    ],
                ],
            ],
        ];

        $product['children'] = [
            [
                'id' => Uuid::randomHex(),
                'productNumber' => $product['productNumber'] . '.01',
                'stock' => 10,
                'active' => true,
                'parentId' => $product['id'],
                'options' => [
                    ['id' => $optionIds['red']],
                    ['id' => $optionIds['xl']],
                ],
            ],
            [
                'id' => Uuid::randomHex(),
                'productNumber' => $product['productNumber'] . '.02',
                'stock' => 10,
                'active' => true,
                'parentId' => $product['id'],
                'options' => [
                    ['id' => $optionIds['green']],
                    ['id' => $optionIds['xl']],
                ],
            ],
            [
                'id' => Uuid::randomHex(),
                'productNumber' => $product['productNumber'] . '.03',
                'stock' => 10,
                'active' => true,
                'parentId' => $product['id'],
                'options' => [
                    ['id' => $optionIds['red']],
                    ['id' => $optionIds['l']],
                ],
            ],
            [
                'id' => Uuid::randomHex(),
                'productNumber' => $product['productNumber'] . '.04',
                'stock' => 10,
                'active' => true,
                'parentId' => $product['id'],
                'options' => [
                    ['id' => $optionIds['green']],
                    ['id' => $optionIds['l']],
                ],
            ],
        ];
    }
}
