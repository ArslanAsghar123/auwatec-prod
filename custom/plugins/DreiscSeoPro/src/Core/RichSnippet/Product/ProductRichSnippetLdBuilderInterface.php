<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\RichSnippet\Product;

use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;

interface ProductRichSnippetLdBuilderInterface
{
    /**
     * @param ProductRichSnippetLdBuilderStruct $productRichSnippetLdBuilderStruct
     * @return array
     */
    public function build(ProductRichSnippetLdBuilderStruct $productRichSnippetLdBuilderStruct): array;
}
