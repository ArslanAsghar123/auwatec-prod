<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\RichSnippet\Product;

use DreiscSeoPro\Core\Content\Customer\CustomerRepository;
use DreiscSeoPro\Core\Content\Product\ProductEnum;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\RichSnippets\Product\GeneralStruct;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\RichSnippets\Product\PriceValidUntilStruct;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\RichSnippets\Product\Review\AuthorStruct;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSettingStruct;
use DreiscSeoPro\Core\Foundation\Seo\SeoUrlAssembler;
use Exception;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Content\Product\Aggregate\ProductReview\ProductReviewEntity;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Framework\Adapter\Translation\AbstractTranslator;
use Shopware\Core\Framework\Adapter\Translation\Translator;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\Currency\CurrencyEntity;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;

class ProductRichSnippetLdBuilder implements ProductRichSnippetLdBuilderInterface
{
    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * @param CustomerRepository $customerRepository
     */
    public function __construct(private readonly SeoUrlAssembler $seoUrlAssembler, private readonly AbstractTranslator $translator, CustomerRepository $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    /**
     * @throws InconsistentCriteriaIdsException
     * @throws Exception
     */
    public function build(ProductRichSnippetLdBuilderStruct $productRichSnippetLdBuilderStruct): array
    {
        $productEntity = $productRichSnippetLdBuilderStruct->getSalesChannelProductEntity();

        /** Translated product */
        $translatedProduct = $productEntity->getTranslated();

        /** Build the ld json */
        $ld = [
            '@context' => 'https://schema.org/',
            '@type' => 'Product',
            'name' => !empty($translatedProduct['name']) ? $translatedProduct['name'] : $productEntity->getName(),
            'description' => !empty($translatedProduct['description']) ? $translatedProduct['description'] : $productEntity->getDescription(),
            'sku' => $this->getSkuByProduct($productRichSnippetLdBuilderStruct, $productEntity),
            'mpn' => $this->getMpnByProduct($productRichSnippetLdBuilderStruct, $productEntity)
        ];

        if(!empty($ld['description'])) {
            /** Remove the html tags from the product description */
            $ld['description'] = strip_tags($ld['description']);

            /** Shorten the description to 5000 characters if it should be longer */
            if(strlen($ld['description']) > (5000-3)) {
                $ld['description'] = substr($ld['description'], 0, (5000-3)) . '...';
            }
        }

        /** Add the manufacturer, if available */
        $ld = $this->addManufacturer($ld, $productRichSnippetLdBuilderStruct);

        /** Add the product images, if available */
        $ld = $this->addMedia($ld, $productRichSnippetLdBuilderStruct);

        /** Add offers, if available */
        $ld = $this->addOffers($ld, $productRichSnippetLdBuilderStruct);

        /** Add aggregate rating, if reviews available */
        $ld = $this->addAggregateRatingAndReviews($ld, $productRichSnippetLdBuilderStruct);

        return $ld;
    }

    private function addManufacturer(array $ld, ProductRichSnippetLdBuilderStruct $productRichSnippetLdBuilderStruct): array
    {
        $productEntity = $productRichSnippetLdBuilderStruct->getSalesChannelProductEntity();

        if (null !== $productEntity->getManufacturer()) {
            /** Translated manufacturer */
            $translatedProductManufacturer = $productEntity->getManufacturer()->getTranslated();

            /** Add info to the ld array */
            $ld['brand'] = [
                '@type' => 'Brand',
                'name' => !empty($translatedProductManufacturer['name']) ? $translatedProductManufacturer['name'] : $productEntity->getManufacturer()->getName()
            ];
        }

        return $ld;
    }

    private function addMedia(array $ld, ProductRichSnippetLdBuilderStruct $productRichSnippetLdBuilderStruct): array
    {
        $productEntity = $productRichSnippetLdBuilderStruct->getSalesChannelProductEntity();

        if (null !== $productEntity->getMedia() && 0 !== $productEntity->getMedia()->count()) {
            $images = [];

            foreach($productEntity->getMedia() as $media) {
                if (null === $media->getMedia()) {
                    continue;
                }

                if(empty($media->getMedia()->getUrl())) {
                    continue;
                }

                $images[] = $media->getMedia()->getUrl();
            }

            if(!empty($images)) {
                $ld['image'] = $images;
            }
        }

        return $ld;
    }

    /**
     * @param SalesChannelProductEntity $productEntity
     * @param SalesChannelEntity $salesChannelEntity
     * @param string|null $salesChannelDomainId
     * @throws InconsistentCriteriaIdsException
     */
    private function addOffers(array $ld, ProductRichSnippetLdBuilderStruct $productRichSnippetLdBuilderStruct): array
    {
        $productEntity = $productRichSnippetLdBuilderStruct->getSalesChannelProductEntity();
        $salesChannelEntity = $productRichSnippetLdBuilderStruct->getSalesChannelEntity();
        $currencyEntity = $productRichSnippetLdBuilderStruct->getCurrencyEntity();
        $salesChannelDomainId = $productRichSnippetLdBuilderStruct->getSalesChannelDomainId();

        $offers = [];

        /** Check for aggregate offer */
        if ($productEntity->getCalculatedPrices()->count() > 1) {
            $offers[] = $this->buildAggregateOffer(
                $productRichSnippetLdBuilderStruct,
                $productEntity,
                $salesChannelEntity,
                $currencyEntity,
                $salesChannelDomainId
            );
        } else {
            $offers[] = $this->buildOffer(
                $productRichSnippetLdBuilderStruct,
                $productEntity,
                $salesChannelEntity,
                $currencyEntity,
                $salesChannelDomainId
            );
        }

        if(!empty($offers)) {
            $ld['offers'] = array_filter($offers);
        }

        return $ld;
    }

    /**
     * @param string|null $salesChannelDomainId
     * @throws InconsistentCriteriaIdsException
     */
    private function buildAggregateOffer(ProductRichSnippetLdBuilderStruct $productRichSnippetLdBuilderStruct, SalesChannelProductEntity $productEntity, SalesChannelEntity $salesChannelEntity, CurrencyEntity $currencyEntity, string $salesChannelDomainId = null): array
    {
        /** Build the base offer info */
        $offer = $this->buildOfferBase(
            'AggregateOffer',
            $productRichSnippetLdBuilderStruct,
            $productEntity,
            $salesChannelEntity,
            $currencyEntity,
            $salesChannelDomainId
        );

        /** Calculate the lowest and the highest price */
        $lowPrice = null;
        $highPrice = null;
        foreach($productEntity->getCalculatedPrices() as $calculatedPrice) {
            if (null === $lowPrice || $calculatedPrice->getUnitPrice() < $lowPrice) {
                $lowPrice = $calculatedPrice->getUnitPrice();
            }

            if (null === $highPrice || $calculatedPrice->getUnitPrice() > $highPrice) {
                $highPrice = $calculatedPrice->getUnitPrice();
            }
        }

        $offer['lowPrice'] = round($lowPrice, 2);
        $offer['highPrice'] = round($highPrice, 2);
        $offer['offerCount'] = $productEntity->getCalculatedPrices()->count();

        /** Set the sub offers */
        $subOffers = [];
        foreach($productEntity->getCalculatedPrices() as $calculatedPrice) {
            $tmpSubOffer = [
                '@type' => 'Offer',
                'priceCurrency' => $offer['priceCurrency'],
                'price' => round($calculatedPrice->getUnitPrice(), 2),
                'availability' => $offer['availability'],
                'itemCondition' => $offer['itemCondition'],
                'url' => !empty($offer['url']) ? $offer['url'] : ''
            ];

            if(!empty($offer['priceValidUntil'])) {
                $tmpSubOffer['priceValidUntil'] = $offer['priceValidUntil'];
            }

            $subOffers[] = $tmpSubOffer;
        }

        $offer['offers'] = $subOffers;

        return $offer;
    }

    /**
     * @param string|null $salesChannelDomainId
     * @throws InconsistentCriteriaIdsException
     */
    private function buildOffer(ProductRichSnippetLdBuilderStruct $productRichSnippetLdBuilderStruct, SalesChannelProductEntity $productEntity, SalesChannelEntity $salesChannelEntity, CurrencyEntity $currencyEntity, string $salesChannelDomainId = null): array
    {
        /** Build the base offer info */
        $offer = $this->buildOfferBase(
            'Offer',
            $productRichSnippetLdBuilderStruct,
            $productEntity,
            $salesChannelEntity,
            $currencyEntity,
            $salesChannelDomainId
        );

        /** Set the product price */
        if ($productEntity->getCalculatedPrices()->count() > 0) {
            $offer['price'] = round($productEntity->getCalculatedPrices()->first()->getUnitPrice(), 2);
        } else {
            $offer['price'] = round($productEntity->getCalculatedPrice()->getUnitPrice(), 2);
        }

        return $offer;
    }

    /**
     * @return array
     * @throws InconsistentCriteriaIdsException
     */
    private function buildOfferBase(string $type, ProductRichSnippetLdBuilderStruct $productRichSnippetLdBuilderStruct, SalesChannelProductEntity $productEntity, SalesChannelEntity $salesChannelEntity, CurrencyEntity $currencyEntity, ?string $salesChannelDomainId)
    {
        $salesChannelContext = $productRichSnippetLdBuilderStruct->getSalesChannelContext();
        $customSetting = $productRichSnippetLdBuilderStruct->getCustomSetting();

        /** Build the base info */
        $offer = [
            '@type' => $type,
            'availability' => $this->getAvailabilityByProduct($productRichSnippetLdBuilderStruct, $productEntity),
            'itemCondition' => $this->getItemConditionByProduct($productRichSnippetLdBuilderStruct, $productEntity),
        ];

        /** Add the currency information */
        $offer['priceCurrency'] = $currencyEntity->getIsoCode();

        /** Calculate the date until the offer is valid */
        $priceValidUntil = $this->getPriceValidUntilByProduct($productRichSnippetLdBuilderStruct, $productEntity);
        if(null !== $priceValidUntil) {
            $offer['priceValidUntil'] = $priceValidUntil;
        }

        /** Try to fetch the url of the product */
        $productUrl = $this->seoUrlAssembler->assemble(
            $productEntity,
            $salesChannelEntity->getId(),
            $salesChannelContext->getLanguageId()
        );

        /** Add the seller, if a value is available */
        if(!empty($customSetting->getRichSnippets()->getProduct()->getOffer()->getSeller()->getName())) {
            $offer['seller'] = [
                '@type' => 'Organization',
                'name' => $customSetting->getRichSnippets()->getProduct()->getOffer()->getSeller()->getName()
            ];
        }

        if(
            null !== $salesChannelDomainId &&
            !empty($productUrl[SeoUrlAssembler::ABSOLUTE_PATHS]) &&
            !empty($productUrl[SeoUrlAssembler::ABSOLUTE_PATHS][$salesChannelDomainId])
        ) {
            $offer['url'] = $productUrl[SeoUrlAssembler::ABSOLUTE_PATHS][$salesChannelDomainId];
        }

        return $offer;
    }

    /**
     * @return array
     * @throws Exception
     */
    private function addAggregateRatingAndReviews(array $ld, ProductRichSnippetLdBuilderStruct $productRichSnippetLdBuilderStruct)
    {
        $productEntity = $productRichSnippetLdBuilderStruct->getSalesChannelProductEntity();
        $reviewLoaderResult = $productRichSnippetLdBuilderStruct->getReviewLoaderResult();

        /** Abort, if no review is available */
        if ($reviewLoaderResult->count() <= 0) {
            return $ld;
        }

        /** Add the reviews */
        $reviews = [];

        /** @var ProductReviewEntity $productReviewEntity */
        $ratingSum = 0;
        $ratingWithPointsCount = 0;
        foreach($reviewLoaderResult as $productReviewEntity) {
            if (null === $productReviewEntity->getCreatedAt()) {
                $datePublished = (new \DateTime('now'))->format('Y-m-d\TH:i:s');
            } else {
                $datePublished = $productReviewEntity->getCreatedAt()->format('Y-m-d\TH:i:s');
            }

            if (null !== $productReviewEntity->getPoints()) {
                $ratingSum+= $productReviewEntity->getPoints();
                $ratingWithPointsCount++;
            }

            $review = [
                '@type' => 'Review',
                'reviewRating' => [
                    "@type" => 'Rating',
                    "ratingValue" => $productReviewEntity->getPoints(),
                    "bestRating" => '5'
                ],
                'datePublished' => $datePublished,
                'name' => $productReviewEntity->getTitle(),
                'description' => $productReviewEntity->getContent()
            ];

            /** Add the author, if available */
            $author = $this->getReviewAuthor($productReviewEntity, $productRichSnippetLdBuilderStruct);
            if (null !== $author) {
                $review['author'] = [
                    '@type' => 'Person',
                    'name' => $author
                ];
            }

            $reviews[] = $review;
        }

        /** Add the aggregate rating */
        $ld['aggregateRating'] = [
            '@type' => 'AggregateRating',
            'ratingValue' => empty($ratingWithPointsCount) || empty($ratingSum) ? 0 : $ratingSum / $ratingWithPointsCount,
            'bestRating' => '5',
            'ratingCount' => $reviewLoaderResult->count()
        ];

        $ld['review'] = $reviews;

        return $ld;
    }

    private function getAvailabilityByProduct(ProductRichSnippetLdBuilderStruct $productRichSnippetLdBuilderStruct, SalesChannelProductEntity $productEntity): string
    {
        $availabilitySettings = $productRichSnippetLdBuilderStruct->getCustomSetting()->getRichSnippets()->getProduct()->getOffer()->getAvailability();
        $customFields = $productEntity->getCustomFields();

        /** Check for custom field value */
        if (!empty($customFields[ProductEnum::CUSTOM_FIELD__DREISC_SEO_RICH_SNIPPET__AVAILABILITY])) {
            $availability = $customFields[ProductEnum::CUSTOM_FIELD__DREISC_SEO_RICH_SNIPPET__AVAILABILITY];
        } elseif (true === $productEntity->getIsCloseout()) {
            if ($productEntity->getStock() > 0) {
                $availability = $availabilitySettings->getDefaultAvailabilityClearanceSale();
            } else {
                $availability = $availabilitySettings->getDefaultAvailabilityClearanceSaleOutOfStock();
            }
        } else {
            if ($productEntity->getStock() > 0) {
                $availability = $availabilitySettings->getDefaultAvailability();
            } else {
                $availability = $availabilitySettings->getDefaultAvailabilityOutOfStock();
            }
        }

        return 'https://schema.org/' . $availability;
    }

    private function getItemConditionByProduct(ProductRichSnippetLdBuilderStruct $productRichSnippetLdBuilderStruct, SalesChannelProductEntity $productEntity): string
    {
        $itemConditionSettings = $productRichSnippetLdBuilderStruct->getCustomSetting()->getRichSnippets()->getProduct()->getOffer()->getItemCondition();
        $itemCondition = $itemConditionSettings->getDefaultItemCondition();
        $customFields = $productEntity->getCustomFields();

        /** Check for custom field value */
        if (!empty($customFields[ProductEnum::CUSTOM_FIELD__DREISC_SEO_RICH_SNIPPET__ITEM_CONDITION])) {
            $itemCondition = $customFields[ProductEnum::CUSTOM_FIELD__DREISC_SEO_RICH_SNIPPET__ITEM_CONDITION];
        }

        return 'https://schema.org/' . $itemCondition;
    }

    private function getSkuByProduct(ProductRichSnippetLdBuilderStruct $productRichSnippetLdBuilderStruct, SalesChannelProductEntity $productEntity): string
    {
        $productGeneralSettings = $productRichSnippetLdBuilderStruct->getCustomSetting()->getRichSnippets()->getProduct()->getGeneral();
        $productNumber = $productEntity->getProductNumber();
        $manufacturerNumber = $productEntity->getManufacturerNumber();
        $customFields = $productEntity->getCustomFields();

        /** Check for custom field value */
        if (!empty($customFields[ProductEnum::CUSTOM_FIELD__DREISC_SEO_RICH_SNIPPET__CUSTOM_SKU])) {
            return $customFields[ProductEnum::CUSTOM_FIELD__DREISC_SEO_RICH_SNIPPET__CUSTOM_SKU];
        }

        if (!empty($manufacturerNumber) && GeneralStruct::SKU_COMPILATION__MANUFACTURER_NUMBER__OTHERWISE__PRODUCT_NUMBER === $productGeneralSettings->getSkuCompilation()) {
            return $manufacturerNumber;
        }

        return $productNumber;
    }

    private function getMpnByProduct(ProductRichSnippetLdBuilderStruct $productRichSnippetLdBuilderStruct, SalesChannelProductEntity $productEntity): string
    {
        $productGeneralSettings = $productRichSnippetLdBuilderStruct->getCustomSetting()->getRichSnippets()->getProduct()->getGeneral();
        $productNumber = $productEntity->getProductNumber();
        $manufacturerNumber = $productEntity->getManufacturerNumber();
        $customFields = $productEntity->getCustomFields();

        /** Check for custom field value */
        if (!empty($customFields[ProductEnum::CUSTOM_FIELD__DREISC_SEO_RICH_SNIPPET__CUSTOM_MPN])) {
            return $customFields[ProductEnum::CUSTOM_FIELD__DREISC_SEO_RICH_SNIPPET__CUSTOM_MPN];
        }

        if (!empty($manufacturerNumber) && GeneralStruct::MPN_COMPILATION__MANUFACTURER_NUMBER__OTHERWISE__PRODUCT_NUMBER === $productGeneralSettings->getMpnCompilation()) {
            return $manufacturerNumber;
        }

        return $productNumber;
    }

    /**
     * @throws Exception
     */
    private function getPriceValidUntilByProduct(ProductRichSnippetLdBuilderStruct $productRichSnippetLdBuilderStruct, SalesChannelProductEntity $productEntity): ?string
    {
        $priceValidUntilSettings = $productRichSnippetLdBuilderStruct->getCustomSetting()->getRichSnippets()->getProduct()->getPriceValidUntil();
        $customFields = $productEntity->getCustomFields();

        /** Check for custom field value */
        if (!empty($customFields[ProductEnum::CUSTOM_FIELD__DREISC_SEO_RICH_SNIPPET__PRICE_VALID_UNTIL_DATE])) {
            return substr((string) $customFields[ProductEnum::CUSTOM_FIELD__DREISC_SEO_RICH_SNIPPET__PRICE_VALID_UNTIL_DATE], 0, 10);
        }

        if (PriceValidUntilStruct::INTERVAL__NOT_DISPLAY === $priceValidUntilSettings->getInterval()) {
            return null;
        }

        $date = new \DateTime('now');

        /** Change the date */
        switch ($priceValidUntilSettings->getInterval()) {
            case PriceValidUntilStruct::INTERVAL__1_DAY:
                $date->add(new \DateInterval('P1D'));
                break;
            case PriceValidUntilStruct::INTERVAL__1_WEEK:
                $date->add(new \DateInterval('P7D'));
                break;
            case PriceValidUntilStruct::INTERVAL__2_WEEK:
                $date->add(new \DateInterval('P14D'));
                break;
            case PriceValidUntilStruct::INTERVAL__1_MONTH:
                $date->add(new \DateInterval('P1M'));
                break;
            case PriceValidUntilStruct::INTERVAL__CUSTOM_DAYS:
                $customDays = $priceValidUntilSettings->getCustomDays();
                if ($customDays > 0) {
                    $modifier = sprintf('P%dD', $customDays);
                    $date->add(new \DateInterval($modifier));
                }
                break;
        }

        return $date->format('Y-m-d');
    }

    /**
     * @return string|null
     * @throws InconsistentCriteriaIdsException
     */
    private function getReviewAuthor(ProductReviewEntity $productReviewEntity, ProductRichSnippetLdBuilderStruct $productRichSnippetLdBuilderStruct)
    {
        $reviewSettings = $productRichSnippetLdBuilderStruct->getCustomSetting()->getRichSnippets()->getProduct()->getReview();
        $authorCompilation = $reviewSettings->getAuthor()->getCompilation();

        /** Return null, if the author should not be displayed */
        if (AuthorStruct::COMPILATION__NOT_DISPLAY === $authorCompilation) {
            return null;
        }

        /** Return the snippet if configured */
        if (AuthorStruct::COMPILATION__STATIC_SNIPPET=== $authorCompilation) {
            return $this->translator->trans('dreiscSeoPro.richSnippets.reviews.defaultAuthor');
        }

        /** Check for fallback, if no customer is available */
        if (null === $productReviewEntity->getCustomerId()) {
            if (null === $productReviewEntity->getExternalUser()) {
                return null;
            }

            return $productReviewEntity->getExternalUser();
        }

        /** Check, if it is a compilation with customer information */
        if (in_array($authorCompilation, [
            AuthorStruct::COMPILATION__FIRSTNAME,
            AuthorStruct::COMPILATION__FIRSTNAME_AND_LASTNAME,
            AuthorStruct::COMPILATION__FIRSTNAME_AND_FIRST_LETTER_OF_LASTNAME
        ], true)) {
            /** Load the information of the customer */
            /** @var CustomerEntity $customerEntity */
            $customerEntity = $this->customerRepository->search(
                new Criteria([ $productReviewEntity->getCustomerId() ])
            )->first();

            /** Abort, if no customer was found */
            if (null === $customerEntity) {
                return null;
            }

            /** Is first name only */
            if(AuthorStruct::COMPILATION__FIRSTNAME === $authorCompilation) {
                return ucfirst($customerEntity->getFirstName());
            }

            /** Is first and last name */
            if(AuthorStruct::COMPILATION__FIRSTNAME_AND_LASTNAME === $authorCompilation) {
                return sprintf(
                    '%s %s',
                    ucfirst($customerEntity->getFirstName()),
                    $customerEntity->getLastName()
                );
            }

            /** Is first and the first letter of the last name */
            if(AuthorStruct::COMPILATION__FIRSTNAME_AND_FIRST_LETTER_OF_LASTNAME === $authorCompilation) {
                return sprintf(
                    '%s %s.',
                    ucfirst($customerEntity->getFirstName()),
                    strtoupper(substr($customerEntity->getLastName(), 0, 1))
                );
            }
        }

        return null;
    }
}
