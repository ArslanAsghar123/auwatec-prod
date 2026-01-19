<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Seo\LiveTemplate;

use DreiscSeoPro\Core\Content\Currency\CurrencyRepository;
use DreiscSeoPro\Core\Seo\SeoDataFetcher\Struct\SeoDataFetchResultStruct;
use Shopware\Core\Checkout\Cart\Price\Struct\CartPrice;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Framework\Api\Context\SalesChannelApiSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\Routing\Exception\LanguageNotFoundException;
use Shopware\Core\System\Currency\CurrencyFormatter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Page\MetaInformation;

class LiveTemplateConverter
{
    /**
     * @var CurrencyRepository
     */
    private $currencyRepository;

    /**
     * @param CurrencyRepository $currencyRepository
     */
    public function __construct(private readonly SystemConfigService $systemConfigService, private readonly CurrencyFormatter $currencyFormatter, CurrencyRepository $currencyRepository)
    {
        $this->currencyRepository = $currencyRepository;
    }

    public function translateProductEntity(?ProductEntity $productEntity, Context $context): void
    {
        /** Abort, if product entity is null */
        if (null === $productEntity) {
            return;
        }

        /** @var SalesChannelApiSource $source */
        $source = $context->getSource();

        /** The live template should only be converted if it is the sales channel api source  */
        if (!$source instanceof SalesChannelApiSource) {
            return;
        }

        /** Calculates the currency price */
        $currencyPrice = $this->fetchCurrencyPrice($productEntity, $context);

        $productEntity->setMetaTitle(
            $this->strReplace(
                '##shopName##',
                $this->systemConfigService->get('core.basicInformation.shopName', $source->getSalesChannelId()),
                $productEntity->getMetaTitle()
            )
        );

        $productEntity->setMetaDescription(
            $this->strReplace(
                '##shopName##',
                $this->systemConfigService->get('core.basicInformation.shopName', $source->getSalesChannelId()),
                $productEntity->getMetaDescription()
            )
        );

        $translated = $productEntity->getTranslated();
        if(!empty($translated)) {
            $translated['metaTitle'] = $this->strReplace(
                '##shopName##',
                $this->systemConfigService->get('core.basicInformation.shopName', $source->getSalesChannelId()),
                $translated['metaTitle']
            );

            $translated['description'] = $this->strReplace(
                '##shopName##',
                $this->systemConfigService->get('core.basicInformation.shopName', $source->getSalesChannelId()),
                $translated['description']
            );

            $translated['metaDescription'] = $this->strReplace(
                '##shopName##',
                $this->systemConfigService->get('core.basicInformation.shopName', $source->getSalesChannelId()),
                $translated['metaDescription']
            );

            $productEntity->setTranslated($translated);
        }

        if (null !== $currencyPrice) {
            $productEntity->setMetaTitle(
                $this->strReplace(
                    '##productPrice##',
                    $currencyPrice,
                    $productEntity->getMetaTitle()
                )
            );

            $productEntity->setMetaDescription(
                $this->strReplace(
                    '##productPrice##',
                    $currencyPrice,
                    $productEntity->getMetaDescription()
                )
            );

            $translated = $productEntity->getTranslated();
            if(!empty($translated)) {
                $translated['metaTitle'] = $this->strReplace(
                    '##productPrice##',
                    $currencyPrice,
                    $translated['metaTitle']
                );

                $translated['description'] = $this->strReplace(
                    '##productPrice##',
                    $currencyPrice,
                    $translated['description']
                );

                $translated['metaDescription'] = $this->strReplace(
                    '##productPrice##',
                    $currencyPrice,
                    $translated['metaDescription']
                );

                $productEntity->setTranslated($translated);
            }
        }
    }

    public function translateSeoDataFetchResultProductPrice(SeoDataFetchResultStruct $seoDataFetchResultStruct, SalesChannelProductEntity $productEntity, SalesChannelContext $salesChannelContext): void
    {
        $currencyPrice = $this->fetchCurrencyPrice($productEntity, $salesChannelContext->getContext());

        if (null !== $currencyPrice) {
            $seoDataFetchResultStruct->setMetaTitle(
                $this->strReplace(
                    '##productPrice##',
                    $currencyPrice,
                    $seoDataFetchResultStruct->getMetaTitle()
                )
            );

            $seoDataFetchResultStruct->setMetaDescription(
                $this->strReplace(
                    '##productPrice##',
                    $currencyPrice,
                    $seoDataFetchResultStruct->getMetaDescription()
                )
            );

            $seoDataFetchResultStruct->setFacebookTitle(
                $this->strReplace(
                    '##productPrice##',
                    $currencyPrice,
                    $seoDataFetchResultStruct->getFacebookTitle()
                )
            );

            $seoDataFetchResultStruct->setFacebookDescription(
                $this->strReplace(
                    '##productPrice##',
                    $currencyPrice,
                    $seoDataFetchResultStruct->getFacebookDescription()
                )
            );

            $seoDataFetchResultStruct->setTwitterTitle(
                $this->strReplace(
                    '##productPrice##',
                    $currencyPrice,
                    $seoDataFetchResultStruct->getTwitterTitle()
                )
            );

            $seoDataFetchResultStruct->setTwitterDescription(
                $this->strReplace(
                    '##productPrice##',
                    $currencyPrice,
                    $seoDataFetchResultStruct->getTwitterDescription()
                )
            );
        }
    }

    public function translateSeoDataFetchResultShopName(SeoDataFetchResultStruct $seoDataFetchResultStruct, SalesChannelContext $salesChannelContext): void
    {
        $seoDataFetchResultStruct->setMetaTitle(
            $this->strReplace(
                '##shopName##',
                $this->systemConfigService->get('core.basicInformation.shopName', $salesChannelContext->getSalesChannelId()),
                $seoDataFetchResultStruct->getMetaTitle()
            )
        );

        $seoDataFetchResultStruct->setMetaDescription(
            $this->strReplace(
                '##shopName##',
                $this->systemConfigService->get('core.basicInformation.shopName', $salesChannelContext->getSalesChannelId()),
                $seoDataFetchResultStruct->getMetaDescription()
            )
        );

        $seoDataFetchResultStruct->setFacebookDescription(
            $this->strReplace(
                '##shopName##',
                $this->systemConfigService->get('core.basicInformation.shopName', $salesChannelContext->getSalesChannelId()),
                $seoDataFetchResultStruct->getFacebookDescription()
            )
        );

        $seoDataFetchResultStruct->setFacebookTitle(
            $this->strReplace(
                '##shopName##',
                $this->systemConfigService->get('core.basicInformation.shopName', $salesChannelContext->getSalesChannelId()),
                $seoDataFetchResultStruct->getFacebookTitle()
            )
        );

        $seoDataFetchResultStruct->setFacebookDescription(
            $this->strReplace(
                '##shopName##',
                $this->systemConfigService->get('core.basicInformation.shopName', $salesChannelContext->getSalesChannelId()),
                $seoDataFetchResultStruct->getFacebookDescription()
            )
        );

        $seoDataFetchResultStruct->setTwitterTitle(
            $this->strReplace(
                '##shopName##',
                $this->systemConfigService->get('core.basicInformation.shopName', $salesChannelContext->getSalesChannelId()),
                $seoDataFetchResultStruct->getTwitterTitle()
            )
        );

        $seoDataFetchResultStruct->setTwitterDescription(
            $this->strReplace(
                '##shopName##',
                $this->systemConfigService->get('core.basicInformation.shopName', $salesChannelContext->getSalesChannelId()),
                $seoDataFetchResultStruct->getTwitterDescription()
            )
        );
    }

    public function translateCategoryEntity(?CategoryEntity $categoryEntity, Context $context): void
    {
        /** Abort, if category entity is null */
        if (null === $categoryEntity) {
            return;
        }

        /** @var SalesChannelApiSource $source */
        $source = $context->getSource();

        /** The live template should only be converted if it is the sales channel api source  */
        if (!$source instanceof SalesChannelApiSource) {
            return;
        }

        $categoryEntity->setMetaTitle(
            $this->strReplace(
                '##shopName##',
                $this->systemConfigService->get('core.basicInformation.shopName', $source->getSalesChannelId()),
                $categoryEntity->getMetaTitle()
            )
        );

        $categoryEntity->setMetaDescription(
            $this->strReplace(
                '##shopName##',
                $this->systemConfigService->get('core.basicInformation.shopName', $source->getSalesChannelId()),
                $categoryEntity->getMetaDescription()
            )
        );

        $translated = $categoryEntity->getTranslated();
        if(!empty($translated)) {
            $translated['metaTitle'] = $this->strReplace(
                '##shopName##',
                $this->systemConfigService->get('core.basicInformation.shopName', $source->getSalesChannelId()),
                $translated['metaTitle']
            );

            $translated['description'] = $this->strReplace(
                '##shopName##',
                $this->systemConfigService->get('core.basicInformation.shopName', $source->getSalesChannelId()),
                $translated['description']
            );

            $categoryEntity->setTranslated($translated);
        }
    }

    /**
     * @return string|null
     * @throws InconsistentCriteriaIdsException
     * @throws LanguageNotFoundException
     */
    private function fetchCurrencyPrice(SalesChannelProductEntity $productEntity, Context $context)
    {
        $currencyPrice = $productEntity->getCurrencyPrice(
            $context->getCurrencyId()
        );

        /** Load the currency iso */
        $currencyEntity = $this->currencyRepository->get($context->getCurrencyId());

        if(!empty($productEntity->getCalculatedCheapestPrice()->getUnitPrice())) {
            return $this->currencyFormatter->formatCurrencyByLanguage(
                $productEntity->getCalculatedCheapestPrice()->getUnitPrice(),
                $currencyEntity->getIsoCode(),
                $context->getLanguageId(),
                $context
            );
        }

        /**
         * Old @deprecated way
         */

        if (null === $currencyPrice) {
            return null;
        }

        if (CartPrice::TAX_STATE_GROSS === $context->getTaxState()) {
            $price = $currencyPrice->getGross();
        } else {
            $price = $currencyPrice->getNet();
        }

        return $this->currencyFormatter->formatCurrencyByLanguage(
            $price,
            $currencyEntity->getIsoCode(),
            $context->getLanguageId(),
            $context
        );
    }

	/**
	 * PHP 8.0 Support
	 */
	public function strReplace($search, $replace, $subject)
	{
		if(empty($search) || empty($replace) || empty($subject)) {
			return $subject;
		}

		return str_replace($search, $replace, (string) $subject);
	}

    public function translateMetaInformation(?MetaInformation $metaInformation, SalesChannelContext $salesChannelContext)
    {
        if (null === $metaInformation) {
            return;
        }

        $metaInformation->setMetaTitle(
            $this->strReplace(
                '##shopName##',
                $this->systemConfigService->get('core.basicInformation.shopName', $salesChannelContext->getSalesChannelId()),
                $metaInformation->getMetaTitle()
            )
        );

        $metaInformation->setMetaDescription(
            $this->strReplace(
                '##shopName##',
                $this->systemConfigService->get('core.basicInformation.shopName', $salesChannelContext->getSalesChannelId()),
                $metaInformation->getMetaDescription()
            )
        );
    }
}
