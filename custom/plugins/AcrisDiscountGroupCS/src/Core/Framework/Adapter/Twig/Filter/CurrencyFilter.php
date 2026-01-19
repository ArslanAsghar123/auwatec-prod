<?php
declare(strict_types=1);

namespace Acris\DiscountGroup\Core\Framework\Adapter\Twig\Filter;

use Acris\DiscountGroup\Storefront\Subscriber\ChangeDecimalPlacesSubscriber;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\DataAbstractionLayer\Pricing\CashRoundingConfig;
use Shopware\Core\System\Currency\CurrencyFormatter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Twig\TwigFilter;

class CurrencyFilter extends \Shopware\Core\Framework\Adapter\Twig\Filter\CurrencyFilter
{
    public function __construct(CurrencyFormatter                                                            $currencyFormatter,
                                private readonly \Shopware\Core\Framework\Adapter\Twig\Filter\CurrencyFilter $parent
    )
    {
        parent::__construct($currencyFormatter);
    }

    /**
     * @return TwigFilter[]
     */
    public function getFilters()
    {
        return parent::getFilters();
    }

    /**
     * @throws InconsistentCriteriaIdsException
     */
    public function formatCurrency($twigContext, $price, $currencyIsoCode = null,
                                   $languageId = null, ?int $decimals = null)
    {
        $context = $twigContext['context'] ?? null;

        if (!$context) {
            return $this->parent->formatCurrency($twigContext, $price, $currencyIsoCode, $languageId, $decimals);
        }

        if (($context instanceof SalesChannelContext) === true) {
            $context = $context->getContext();
        }

        if (($context instanceof Context) === false) {
            return $this->parent->formatCurrency($twigContext, $price, $currencyIsoCode, $languageId, $decimals);
        }


        $originalRoundingConfig = $context
            ->getExtension(ChangeDecimalPlacesSubscriber::ORIGINAL_ROUNDING_EXTENSION_KEY);

        if (($originalRoundingConfig instanceof CashRoundingConfig) === false) {
            return $this->parent->formatCurrency($twigContext, $price, $currencyIsoCode, $languageId, $decimals);
        }

        $originalRoundingDecimals = $originalRoundingConfig->getDecimals();

        if (is_int($price) === false && is_float($price) === false) {
            return $this->parent->formatCurrency($twigContext, $price, $currencyIsoCode, $languageId,
                $originalRoundingDecimals);
        }
        $price = round($price, $originalRoundingDecimals);
        return $this->parent->formatCurrency($twigContext, $price, $currencyIsoCode, $languageId,
            $originalRoundingDecimals);
    }

}