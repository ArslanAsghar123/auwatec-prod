<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Foundation\CustomSettingEntity;

use DreiscSeoPro\Core\Cache\MessageBusCacheInvalidator;
use DreiscSeoPro\Core\Foundation\Dal\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;

class CustomSettingEntityService
{
    static private array $cachedSettings = [];
    static private int $cachedTimestamp = 0;

    public function __construct(
        private readonly MessageBusCacheInvalidator $messageBusCacheInvalidator
    ) { }

    /**
     * @throws InconsistentCriteriaIdsException
     */
    public function set(CustomSettingEntityStruct $customSettingEntityStruct, string $key, array $value): void
    {
        /** Trim key */
        $key = trim($key);

        /** Check, if there is already an entry */
        $customSettingId = $this->getCustomSettingId($customSettingEntityStruct, $key);

        /** We remove the entry if already exists */
        if (null !== $customSettingId) {
            $customSettingEntityStruct->getEntityRepository()->deleteByCriteria(
                new Criteria([ $customSettingId ])
            );
        }

        /** We insert the new setting */
        $customSettingEntityStruct->getEntityRepository()->upsert([
            [
                'id' => Uuid::randomHex(),
                'key' => $key,
                'value' => $value,
                'salesChannelId' => $customSettingEntityStruct->getSalesChannelId()
            ]
        ]);

        /** We reset the cache after setting a new value */
        $this->resetCache();
    }

    /**
     * @return array|mixed|null
     * @throws InconsistentCriteriaIdsException
     */
    public function get(CustomSettingEntityStruct $customSettingEntityStruct, string $key)
    {
        $config = $this->load($customSettingEntityStruct);
        $parts = explode('.', trim($key));
        $pointer = $config;

        foreach ($parts as $part) {
            if (!\is_array($pointer)) {
                return null;
            }

            if (\array_key_exists($part, $pointer)) {
                $pointer = $pointer[$part];
                continue;
            }

            return null;
        }

        return $pointer;
    }

    /**
     * @throws InconsistentCriteriaIdsException
     */
    public function keyExists(CustomSettingEntityStruct $customSettingEntityStruct, string $key): bool
    {
        return null !== $this->get($customSettingEntityStruct, $key);
    }

    /**
     * @throws InconsistentCriteriaIdsException
     */
    public function load(CustomSettingEntityStruct $customSettingEntityStruct): array
    {
        $settingCacheId = $this->getSettingCacheId($customSettingEntityStruct);

        /** Check for cached values */
        if ($this->messageBusCacheInvalidator->isValid(self::$cachedTimestamp) && !empty(self::$cachedSettings[$settingCacheId])) {
            return self::$cachedSettings[$settingCacheId];
        }

        /** Prepare cache */
        self::$cachedSettings[$settingCacheId] = [];

        /** Fetch all settings for the given sales channel */
        $customSettingSearchResult = $this->fetchCustomSettingsBySalesChannelId(
            $customSettingEntityStruct,
            $customSettingEntityStruct->getSalesChannelId()
        );

        /** Build the config array */
        $systemConfigArray = $this->buildSystemConfigArray(
            $customSettingEntityStruct,
            $customSettingSearchResult->getEntities()
        );

        if (null !== $customSettingEntityStruct->getSalesChannelId() && true === $customSettingEntityStruct->isMergeDefaultToSalesChannel()) {
            /** We also load the default settings, because we have to merge it with the sales channel settings */
            $defaultCustomSettingSearchResult = $this->fetchCustomSettingsBySalesChannelId($customSettingEntityStruct, null);

            /** Build also the config array of the default config */
            $defaultSystemConfigArray = $this->buildSystemConfigArray(
                $customSettingEntityStruct,
                $defaultCustomSettingSearchResult->getEntities()
            );

            /** Merge default settings to the sales channel settings */
            $systemConfigArray = $this->mergeSystemConfigs($defaultSystemConfigArray, $systemConfigArray);
        }


        /** Store the config array in the singleton cache */
        self::$cachedSettings[$settingCacheId] = $systemConfigArray;
        self::$cachedTimestamp = time();

        return self::$cachedSettings[$settingCacheId];
    }

    public function resetCache(): void
    {
        /** We update the cache timestamp */
        $this->messageBusCacheInvalidator->updateLastCacheTimestamp();

        self::$cachedSettings = [];
    }

    /**
     * @param string|null $salesChannelId
     */
    private function fetchCustomSettingsBySalesChannelId(CustomSettingEntityStruct $customSettingEntityStruct, string $salesChannelId = null): EntitySearchResult
    {
        return $customSettingEntityStruct->getEntityRepository()->search(
            (new Criteria())
                ->addFilter(
                    new EqualsFilter(
                        $customSettingEntityStruct->getSalesChannelIdField(),
                        $salesChannelId
                    )
                )
        );
    }

    private function buildSystemConfigArray(CustomSettingEntityStruct $customSettingEntityStruct, EntityCollection $entityCollection): array
    {
        $result = [];

        /** @var Entity $entity */
        foreach ($entityCollection as $entity) {
            $keys = explode('.', (string) $entity->get($customSettingEntityStruct->getKeyField()));

            $result = $this->getSubArray(
                $result,
                $keys,
                $entity->get($customSettingEntityStruct->getValueField())
            );
        }

        return $result;
    }

    private function getSubArray(array $configValues, array $keys, $value): array
    {
        $key = array_shift($keys);

        if (empty($keys)) {
            if ($value !== false && empty($value)) {
                return $configValues;
            }

            $configValues[$key] = $value;
        } else {
            if (!\array_key_exists($key, $configValues)) {
                $configValues[$key] = [];
            }

            $configValues[$key] = $this->getSubArray($configValues[$key], $keys, $value);
        }

        return $configValues;
    }

    private function getSettingCacheId(CustomSettingEntityStruct $customSettingEntityStruct): string
    {
        return sprintf(
            '%s/%s',
            $customSettingEntityStruct->getSalesChannelId() ?? 'global',
            $customSettingEntityStruct->isMergeDefaultToSalesChannel()
        );
    }

    private function getCustomSettingId(CustomSettingEntityStruct $customSettingEntityStruct, string $key): ?string
    {
        $idSearchResult = $customSettingEntityStruct->getEntityRepository()->searchIds(
            (new Criteria())
                ->addFilter(
                    new EqualsFilter($customSettingEntityStruct->getKeyField(), $key),
                    new EqualsFilter($customSettingEntityStruct->getSalesChannelIdField(), $customSettingEntityStruct->getSalesChannelId())
                )
        );

        if (0 === $idSearchResult->getTotal()) {
            return null;
        }

        return $idSearchResult->firstId();
    }

    private function mergeSystemConfigs(array $defaultSystemConfigArray, array $salesChannelSystemConfigArray): array
    {
        foreach($defaultSystemConfigArray as $configKey => $defaultValue) {
            if (is_array($defaultValue)) {
                if (isset($salesChannelSystemConfigArray[$configKey])) {
                    /** Merge the sub array with the sales channel values */
                    $defaultSystemConfigArray[$configKey] = $this->mergeSystemConfigs($defaultSystemConfigArray[$configKey], $salesChannelSystemConfigArray[$configKey]);
                }
            } else {
                if (isset($salesChannelSystemConfigArray[$configKey])) {
                    /** The value is overridden in the sales channel setting, so we set the sales channel value */
                    $defaultSystemConfigArray[$configKey] = $salesChannelSystemConfigArray[$configKey];
                }
            }
        }

        /** Check for array items, which are in the sales channel array but not in the default array */
        $missingDefaultKeys = array_diff(array_keys($salesChannelSystemConfigArray), array_keys($defaultSystemConfigArray));

        /** Iterate all missing default array keys */
        foreach($missingDefaultKeys as $missingDefaultKey) {
            /** Apply the sales channel values to the default array */
            $defaultSystemConfigArray[$missingDefaultKey] = $salesChannelSystemConfigArray[$missingDefaultKey];
        }

        return $defaultSystemConfigArray;
    }

    private function isEmptyValue($value): bool
    {
        if(is_array($value) && empty($value)) {
            return true;
        }

        return false;
    }
}
