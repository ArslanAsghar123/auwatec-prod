<?php

namespace AkuCmsFactory\Services;

use Shopware\Core\Content\Cms\CmsPageEvents;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
use Shopware\Core\Framework\Struct\StructCollection;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Content\ProductStream\Service\ProductStreamBuilderInterface;
use Shopware\Core\Content\Product\SalesChannel\Listing\ProductListingLoader;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;

class CmsElementService {

    protected $cmsFactoryElementRepository;
    protected $mediaRepository;
    protected $categoryRepository;
    protected $productRepository;
    protected $productStreamRepository;
    protected $productManufacturerRepository;
    protected $salesChannelProductRepository;
    protected $salesChannelCategoryRepository;
    protected $productStreamBuilder;
    protected $listingLoader;

    protected $definitionInstanceRegistry;

    public function __construct(
        EntityRepository              $cmsFactoryElementRepository,
        EntityRepository              $mediaRepository,
        EntityRepository              $categoryRepository,
        EntityRepository              $productRepository,
        EntityRepository              $productStreamRepository,
        EntityRepository              $productManufacturerRepository,
        SalesChannelRepository        $salesChannelProductRepository,
        SalesChannelRepository        $salesChannelCategoryRepository,
        ProductStreamBuilderInterface $productStreamBuilder,
        ProductListingLoader          $listingLoader,
        DefinitionInstanceRegistry    $definitionInstanceRegistry,
    ) {
        $this->cmsFactoryElementRepository = $cmsFactoryElementRepository;
        $this->mediaRepository = $mediaRepository;
        $this->categoryRepository = $categoryRepository;
        $this->productRepository = $productRepository;
        $this->productManufacturerRepository = $productManufacturerRepository;
        $this->productStreamRepository = $productStreamRepository;
        $this->salesChannelProductRepository = $salesChannelProductRepository;
        $this->salesChannelCategoryRepository = $salesChannelCategoryRepository;
        $this->productStreamBuilder = $productStreamBuilder;
        $this->listingLoader = $listingLoader;
        $this->definitionInstanceRegistry = $definitionInstanceRegistry;
    }

    /**
     * @param array $fields fields array as stored in CmsFacoryElement.fields
     * @param array $field_values array as stored in config.field_values.value
     * @param $context - required for DB access
     */

    public function getData($fields, $field_values, $context, $sales_channel_context = null) {
        //var_dump(get_class($context), get_class($sales_channel_context));

        $data = [];
        if (!is_array($fields)) {
            return $data;
        }
        foreach ($fields as $field) {
            $name = $field['name'];
            $type = $field['type'];
            $associations = isset($field['associations']) ? $field['associations'] : [];
            $defaultValue = $field['defaultValue'];
            if (in_array($type, ['text', 'textarea', 'text-editor', 'color'])) {
                $data[$name] = isset($field_values[$name])
                    ? $field_values[$name]
                    : $defaultValue;
            }
            if ('media' == $type) {
                $media_id = isset($field_values[$name])
                    ? $field_values[$name]
                    : null;
                $media = null;
                if ($media_id && Uuid::isValid($media_id)) {
                    $criteria = new Criteria([$media_id]);
                    $associations_available = [
                        'custom_fields',
                    ];
                    $associations_required = array_intersect($associations_available, $associations);
                    foreach ($associations_required as $assoc) {
                        $criteria->addAssociation($assoc);
                    }
                    $criteria->setLimit(1);
                    $media = $this->mediaRepository
                        ->search($criteria, $context)->getEntities()->first();
                }
                $data[$name] = $media;
            }
            if ('gallery' == $type) {
                $media_ids = isset($field_values[$name]) && is_array($field_values[$name])
                    ? $field_values[$name]
                    : [];
                $media = [];
                $media_ids = array_filter($media_ids, function ($media_id) {
                    return Uuid::isValid($media_id);
                });
                $media_ids = array_values($media_ids);
                if (0 < count($media_ids)) {
                    $criteria = new Criteria($media_ids);
                    $associations_available = [
                        'custom_fields',
                    ];
                    $associations_required = array_intersect($associations_available, $associations);
                    foreach ($associations_required as $assoc) {
                        $criteria->addAssociation($assoc);
                    }

                    $media = $this->mediaRepository
                        ->search($criteria, $context)->getEntities();
                }
                $data[$name] = $media;
            }
            if ('category' == $type) {
                $category_id = isset($field_values[$name])
                    ? $field_values[$name]
                    : null;
                $category = null;
                if ($category_id && Uuid::isValid($category_id)) {
                    $sales_channel_id = null;
                    if ($sales_channel_context && method_exists($sales_channel_context, 'getSalesChannel')) {
                        $sales_channel = $sales_channel_context->getSalesChannel();
                        if ($sales_channel) {
                            $sales_channel_id = $sales_channel->getId();
                        }
                    }
                    $criteria = new Criteria([$category_id]);
                    $associations_available = [
                        'media', 'custom_fields',
                        'children', 'children.media', 'children.custom_fields',
                        'products', 'products.cover', 'products.media', 'products.custom_fields',
                        'products.children', 'products.children.cover', 'products.children.media', 'products.children.custom_fields',
                    ];
                    $associations_required = array_intersect($associations_available, $associations);
                    $childrenDepth = isset($field['childrenDepth']) ? $field['childrenDepth'] : 1;
                    if (in_array('children', $associations_required) && 1 < $childrenDepth) {
                        $children_associations = array_intersect(['children', 'children.media', 'children.custom_fields'], $associations_required);
                        foreach ($children_associations as $assoc) {
                            for ($i = 1; $i < $childrenDepth; $i++) {
                                $a = array_fill(0, $i, 'children');
                                $associations_required[] = implode('.', $a) . "." . $assoc;
                            }
                        }
                    }
                    foreach ($associations_required as $assoc) {
                        $criteria->addAssociation($assoc);
                    }
                    if (in_array('products', $associations_required)) {
                        $criteria->addAssociation('products.options.group');

                        if (in_array('products.children', $associations_required)) {
                            $criteria->addAssociation('products.children.options.group');
                        }
                        if ($sales_channel_id) {
                            $criteria->addAssociation('products.visibilities');
                        }
                    }
                    $criteria->setLimit(1);
                    if ($sales_channel_context) {
                        $category = $this->salesChannelCategoryRepository
                            ->search($criteria, $sales_channel_context)->getEntities()->first();
                        if ($category) {
                            $products = $category->getProducts();
                            if (null !== $products && 0 < count($products) && $sales_channel_id) {
                                $filtered_product_list = [];
                                foreach ($products as $product) {
                                    $visibilities = $product->getVisibilities();
                                    if (0 < count($visibilities)) {
                                        foreach ($visibilities as $visibility) {
                                            if ($visibility->getSalesChannelId() == $sales_channel_id) {
                                                $filtered_product_list[] = $product;
                                            }
                                        }
                                    }
                                }
                                $category->setProducts(new ProductCollection($filtered_product_list));
                            }
                        }
                    } else {
                        $category = $this->categoryRepository
                            ->search($criteria, $context)->getEntities()->first();
                    }
                }
                $data[$name] = $category;
            }
            if ('product' == $type) {
                $product_id = isset($field_values[$name])
                    ? $field_values[$name]
                    : null;
                $product = null;
                if ($product_id && Uuid::isValid($product_id)) {
                    $criteria = new Criteria([$product_id]);
                    $associations_available = [
                        'media', 'custom_fields', 'cover', 'properties',
                        'children', 'children.cover', 'children.media', 'children.custom_fields',
                        'crossSellings.assignedProducts', 'crossSellings.assignedProducts.product.cover', 'crossSellings.assignedProducts.product.media', 'crossSellings.assignedProducts.product.custom_fields',
                    ];
                    $associations_required = array_intersect($associations_available, $associations);
                    foreach ($associations_required as $assoc) {
                        $criteria->addAssociation($assoc);
                    }
                    $criteria->addAssociation('options.group');
                    if (in_array('children', $associations_required)) {
                        $criteria->addAssociation('children.options.group');
                    }
                    if (in_array('properties', $associations_required)) {
                        $criteria->addAssociation('properties.group');
                        if (in_array('children', $associations_required)) {
                            $criteria->addAssociation('children.properties.group');
                        }
                    }

                    $criteria->setLimit(1);
                    if ($sales_channel_context) {
                        $product = $this->salesChannelProductRepository
                            ->search($criteria, $sales_channel_context)->getEntities()->first();
                    } else {
                        $product = $this->productRepository
                            ->search($criteria, $context)->getEntities()->first();
                    }
                }
                $data[$name] = $product;
            }
            if ('manufacturer' == $type) {
                $manufacturer_ids = isset($field_values[$name])
                    ? $field_values[$name]
                    : null;
                if ($manufacturer_ids != null && is_array($manufacturer_ids) && count($manufacturer_ids) > 0) {
                    $criteria = new Criteria($manufacturer_ids);
                    $associations_available = ['media', 'custom_fields'];
                    $associations_required = array_intersect($associations_available, $associations);
                    foreach ($associations_required as $assoc) {
                        $criteria->addAssociation($assoc);
                    }
                    $manufacturers = $this->productManufacturerRepository->search($criteria, $context)->getEntities()->getElements();

                    $data[$name] = $manufacturers;
                }
            }
            if ('custom-entity' == $type) {
                if ($field_values && $field_values[$name] != null && is_array($field_values[$name]) && count($field_values[$name]) > 0) {
                    $customRepository = $this->definitionInstanceRegistry->getRepository($field["entity"]);
                    $customCriteria = new Criteria($field_values[$name]);
                    foreach ($field['associations'] as $assoc) {
                        $customCriteria->addAssociation(trim($assoc));
                    }
                    $entities = $customRepository->search($customCriteria, $context)->getEntities()->getElements();
                    $data[$name] = $entities;
                }
            }

            if ('stream' == $type) {

                $product_stream_id = isset($field_values[$name])
                    ? $field_values[$name]
                    : null;
                $product_stream = null;
                $p_criteria = new Criteria();
                if ($product_stream_id && Uuid::isValid($product_stream_id)) {
                    $criteria = new Criteria([$product_stream_id]);
                    $criteria->setLimit(1);
                    $product_stream = $this->productStreamRepository
                        ->search($criteria, $context)->getEntities()->first();
                    // von hier:
                    // Shopware\Core\Content\Product\SalesChannel\CrossSelling\ProductCrossSellingRoute::loadByStream

                    $filters = $this->productStreamBuilder->buildFilters(
                        $product_stream_id,
                        $context
                    );
                    $p_criteria->addFilter(...$filters);

                    $limit = isset($field_values[$name . '_limit']) ? intval($field_values[$name . '_limit']) : 10;
                    //var_dump($limit);die();
                    $p_criteria->setLimit($limit);
                    $sorting = $limit = isset($field_values[$name . '_sorting']) ? trim($field_values[$name . '_sorting']) : 'name:ASC';
                    $parts = explode(':', $sorting);
                    $sort_by = 'name';
                    if (in_array($parts[0], ['name', 'createdAt', 'cheapestPrice', 'random'])) {
                        $sort_by = $parts[0];
                    }
                    $sort_dir = FieldSorting::ASCENDING;
                    if (isset($parts[1]) && 'DESC' == $parts[1]) {
                        $sort_dir = FieldSorting::DESCENDING;
                    }
                    // Todo: random sorting funktioniert so nicht.
                    if ($sort_by != 'random') {
                        $p_criteria->addSorting(new FieldSorting($sort_by, $sort_dir));
                    }

                    $products = [];
                    if ($sales_channel_context !== null && $product_stream != null) {
                        $searchResult = $this->listingLoader->load($p_criteria, $sales_channel_context);
                        $products = $searchResult->getEntities();
                        $product_stream->addExtension('products', $products);
                    }
                }
                $data[$name] = $product_stream;
            }

            if ('choice' == $type) {
                $value = isset($field_values[$name])
                    ? $field_values[$name]
                    : $defaultValue;
                $data[$name] = $value;
            }

            if ('checkbox' == $type) {
                $value = isset($field_values[$name])
                    ? $field_values[$name]
                    : $defaultValue;
                $data[$name] = $value;
            }

            if ('repeater' == $type) {
                $vals = isset($field_values[$name]) && is_array($field_values[$name])
                    ? $field_values[$name]
                    : [];
                $subdata = [];
                foreach ($vals as $subvals) {
                    $subdata[] = $this->getData($field['children'], $subvals, $context, $sales_channel_context);
                }
                $data[$name] = $subdata;
            }

        }

        return $data;

    }

}