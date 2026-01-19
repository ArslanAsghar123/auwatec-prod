<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\RichSnippets;

use DreiscSeoPro\Core\CustomSetting\Struct\AbstractCustomSettingStruct;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\RichSnippets\Product\GeneralStruct as ProductGeneralStruct;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\RichSnippets\Product\OfferStruct;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\RichSnippets\Product\PriceValidUntilStruct;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\RichSnippets\Product\ReviewStruct;

class ProductStruct extends AbstractCustomSettingStruct
{
    final const DEFAULT__SKU_COMPILATION = ProductGeneralStruct::SKU_COMPILATION__PRODUCT_NUMBER;
    final const DEFAULT__MPN_COMPILATION = ProductGeneralStruct::MPN_COMPILATION__MANUFACTURER_NUMBER__OTHERWISE__PRODUCT_NUMBER;
    final const DEFAULT__PRICE_VALID_UNTIL__INTERVAL = PriceValidUntilStruct::INTERVAL__TODAY;
    final const DEFAULT__PRICE_VALID_UNTIL__CUSTOM_DAYS = 0;

    /**
     * @var ProductGeneralStruct
     */
    protected $general;

    /**
     * @var OfferStruct
     */
    protected $offer;

    /**
     * @var PriceValidUntilStruct
     */
    protected $priceValidUntil;

    /**
     * @var ReviewStruct
     */
    protected $review;

    /**
     * @param array $productSettings
     * @param string $settingContext
     */
    public function __construct(array $productSettings, string $settingContext)
    {
        parent::__construct($settingContext);

        $this->general = new ProductGeneralStruct(
            !empty($productSettings['general']['skuCompilation']) ? $productSettings['general']['skuCompilation'] : $this->setDefault(self::DEFAULT__SKU_COMPILATION),
            !empty($productSettings['general']['mpnCompilation']) ? $productSettings['general']['mpnCompilation'] : $this->setDefault(self::DEFAULT__MPN_COMPILATION),
            $settingContext
        );

        $this->offer = new OfferStruct(
            !empty($productSettings['offer']) ? $productSettings['offer'] : [],
            $settingContext
        );

        $this->priceValidUntil = new PriceValidUntilStruct(
            !empty($productSettings['priceValidUntil']['interval']) ? $productSettings['priceValidUntil']['interval'] : $this->setDefault(self::DEFAULT__PRICE_VALID_UNTIL__INTERVAL),
            !empty($productSettings['priceValidUntil']['customDays']) ? $productSettings['priceValidUntil']['customDays'] : $this->setDefault(self::DEFAULT__PRICE_VALID_UNTIL__CUSTOM_DAYS),
            $settingContext
        );

        $this->review = new ReviewStruct(
            !empty($productSettings['review']) ? $productSettings['review'] : [],
            $settingContext
        );
    }

    public function getGeneral(): ProductGeneralStruct
    {
        return $this->general;
    }

    public function setGeneral(ProductGeneralStruct $general): ProductStruct
    {
        $this->general = $general;

        return $this;
    }

    public function getOffer(): OfferStruct
    {
        return $this->offer;
    }

    public function setOffer(OfferStruct $offer): ProductStruct
    {
        $this->offer = $offer;

        return $this;
    }

    public function getPriceValidUntil(): PriceValidUntilStruct
    {
        return $this->priceValidUntil;
    }

    public function setPriceValidUntil(PriceValidUntilStruct $priceValidUntil): ProductStruct
    {
        $this->priceValidUntil = $priceValidUntil;

        return $this;
    }

    public function getReview(): ReviewStruct
    {
        return $this->review;
    }

    public function setReview(ReviewStruct $review): ProductStruct
    {
        $this->review = $review;

        return $this;
    }
}
