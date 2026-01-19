<?php

namespace Intedia\Doofinder\Core\Content\ProductExport\Decorator;

use Shopware\Core\Content\ProductExport\ProductExportEntity;
use Shopware\Core\Content\ProductExport\Service\ProductExportRendererInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class ProductExportRendererDecorator implements ProductExportRendererInterface
{
    /** @var ProductExportRendererInterface $productExportRenderer */
    private ProductExportRendererInterface $productExportRenderer;

    /**
     * @param ProductExportRendererInterface $productExportRenderer
     */
    public function __construct(ProductExportRendererInterface $productExportRenderer)
    {
        $this->productExportRenderer = $productExportRenderer;
    }

    public function renderHeader(
        ProductExportEntity $productExport,
        SalesChannelContext $salesChannelContext
    ): string {
        return $this->productExportRenderer->renderHeader($productExport, $salesChannelContext);
    }

    public function renderFooter(
        ProductExportEntity $productExport,
        SalesChannelContext $salesChannelContext
    ): string {
        return $this->productExportRenderer->renderFooter($productExport, $salesChannelContext);
    }

    public function renderBody(
        ProductExportEntity $productExport,
        SalesChannelContext $salesChannelContext,
        array $data
    ): string {
        if (array_key_exists('DooFinderVariantInformation', $salesChannelContext->getVars()) &&
            $salesChannelContext->getVars()['DooFinderVariantInformation']) {
            $return = '';
            if ($data['product']->getChildren() && !$productExport->isIncludeVariants()) {
                $return .= $this->productExportRenderer->renderBody($productExport, $salesChannelContext, $data);

                foreach ($data['product']->getChildren() as $child) {
                    $childData = $data;
                    $childData['product'] = $child;

                    $return .= $this->productExportRenderer->renderBody($productExport, $salesChannelContext, $childData);
                }
            } else {
                return $this->productExportRenderer->renderBody($productExport, $salesChannelContext, $data);
            }
            return $return;
        }

        return $this->productExportRenderer->renderBody($productExport, $salesChannelContext, $data);
    }
}