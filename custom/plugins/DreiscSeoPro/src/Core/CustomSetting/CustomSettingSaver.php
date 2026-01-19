<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\CustomSetting;

use DreiscSeoPro\Core\Content\DreiscSeoSetting\DreiscSeoSettingRepository;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSettingStruct;
use DreiscSeoPro\Core\Foundation\CustomSettingEntity\CustomSettingEntityService;
use DreiscSeoPro\Core\Foundation\CustomSettingEntity\CustomSettingEntityStruct;
use RuntimeException;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;

class CustomSettingSaver
{
    final const SUPPORTED_SAVE_PATHS = [
        'metaTags.metaTitle.lengthConfig',
        'metaTags.metaDescription.lengthConfig',
        'metaTags.keywords.lengthConfig',
        'metaTags.robotsTag',

        'socialMedia.facebookTitle.lengthConfig',
        'socialMedia.facebookDescription.lengthConfig',
        'socialMedia.twitterTitle.lengthConfig',
        'socialMedia.twitterDescription.lengthConfig',

        'richSnippets.general',
        'richSnippets.product.general',
        'richSnippets.product.priceValidUntil',
        'richSnippets.product.review.author',
        'richSnippets.product.offer.seller',
        'richSnippets.product.offer.availability',
        'richSnippets.product.offer.itemCondition',
        'richSnippets.breadcrumb.general',
        'richSnippets.breadcrumb.home',
        'richSnippets.breadcrumb.product',
        'richSnippets.localBusiness.general',
        'richSnippets.localBusiness.address',
        'richSnippets.localBusiness.openingHoursSpecification',
        'richSnippets.logo.general',

        'seoUrl',
        'serp',

        'sitemap.general',

        'canonical.general',

        'bulkGenerator.general',
        'bulkGenerator.general.startGeneratorInTheStorageProcess',

        'robotsTxt',

        'ai.openAi'
    ];

    /**
     * @var DreiscSeoSettingRepository
     */
    private $dreiscSeoSettingRepository;

    /**
     * @param DreiscSeoSettingRepository $dreiscSeoSettingRepository
     */
    public function __construct(DreiscSeoSettingRepository $dreiscSeoSettingRepository, private readonly CustomSettingEntityService $customSettingEntityService)
    {
        $this->dreiscSeoSettingRepository = $dreiscSeoSettingRepository;
    }

    /**
     * @throws InconsistentCriteriaIdsException
     */
    public function save(CustomSettingStruct|array $customSetting, string $salesChannelId = null): void
    {
        /** Validate type */
        if (!$customSetting instanceof CustomSettingStruct && !is_array($customSetting)) {
            throw new RuntimeException('Invalid type. CustomSettingStruct or array are valid');
        }

        /** Convert to array, if it's a struct */
        if ($customSetting instanceof CustomSettingStruct) {
            $customSetting = $customSetting->toArray();
        }

        /** Save each supported save paths */
        foreach(self::SUPPORTED_SAVE_PATHS as $path) {
            $this->savePath($path, $customSetting, $salesChannelId);
        }
    }

    /**
     * @param $path
     * @throws InconsistentCriteriaIdsException
     */
    private function savePath($path, array $customSetting, string $salesChannelId = null): void
    {
        $pathCustomSetting = $this->getCustomSettingByPath($path, $customSetting);

        /** Abort, if there a no data for the current path */
        if (null === $pathCustomSetting) {
            return;
        }

        /** Save the path */
        $customSettingEntityStruct = new CustomSettingEntityStruct(
            $this->dreiscSeoSettingRepository,
            $salesChannelId
        );

        $this->customSettingEntityService->set(
            $customSettingEntityStruct,
            $path,
            $pathCustomSetting
        );
    }

    /**
     * @param $path
     * @return array|mixed|null
     */
    private function getCustomSettingByPath($path, array $customSetting)
    {
        $pathParts = explode('.', (string) $path);
        $pointer = $customSetting;

        foreach($pathParts as $pathPart) {
            if (!isset($pointer[$pathPart]) || !is_array($pointer[$pathPart])) {
                return null;
            }

            $pointer = $pointer[$pathPart];
        }

        return $pointer;
    }
}
