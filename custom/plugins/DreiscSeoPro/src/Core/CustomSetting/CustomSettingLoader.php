<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\CustomSetting;

use DreiscSeoPro\Core\Content\DreiscSeoSetting\DreiscSeoSettingRepository;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSettingStruct;
use DreiscSeoPro\Core\Foundation\CustomSettingEntity\CustomSettingEntityService;
use DreiscSeoPro\Core\Foundation\CustomSettingEntity\CustomSettingEntityStruct;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;

class CustomSettingLoader
{
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
     * @param string|null $salesChannelId
     * @throws InconsistentCriteriaIdsException
     */
    public function load(string $salesChannelId = null, bool $mergeDefaultToSalesChannel = false): CustomSettingStruct
    {
        $customSettings = $this->customSettingEntityService->load(
            new CustomSettingEntityStruct(
                $this->dreiscSeoSettingRepository,
                $salesChannelId,
                $mergeDefaultToSalesChannel
            )
        );

        /** Setting context */
        $settingContext = CustomSettingStruct::SETTING_CONTEXT__DEFAULT;
        if (null !== $salesChannelId && false === $mergeDefaultToSalesChannel) {
            $settingContext = CustomSettingStruct::SETTING_CONTEXT__SALES_CHANNEL;
        }

        return new CustomSettingStruct(
            $customSettings,
            $settingContext
        );
    }
}
