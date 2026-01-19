<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Content\Language;

use DreiscSeoPro\Core\Foundation\Dal\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\Language\LanguageEntity;

/**
* @method LanguageEntity    get(string $id, array $associations = null, ?Context $context = null, $disableCache = false)
* @method LanguageSearchResult    search(Criteria $criteria, Context $context = null, $disableCache = false)
*/
class LanguageRepository extends EntityRepository
{
    static public array $cachedLanguageRepositories = [];

    public function getCached(string $languageId, ?array $associations, Context $languageChainContext)
    {
        $cacheKey = sprintf(
            '%s-%s-%s',
            $languageId,
            $languageChainContext->getLanguageId(),
            !empty($associations) ? md5(implode(',', $associations)) : '-'
        );

        if(empty(self::$cachedLanguageRepositories[$cacheKey])) {
            self::$cachedLanguageRepositories[$cacheKey] = $this->get($languageId, $associations, $languageChainContext);
        }

        return self::$cachedLanguageRepositories[$cacheKey];
    }
}

