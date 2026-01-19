<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Seo\SeoUrl;

use DreiscSeoPro\Core\Content\SeoUrl\SeoUrlRepository;
use DreiscSeoPro\Core\Seo\SeoUrlSlugify;
use Shopware\Core\Content\Seo\SeoUrl\SeoUrlEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\Uuid\Uuid;

class SeoUrlSaver
{
    public function __construct(private readonly SeoUrlRepository $seoUrlRepository, private readonly SeoUrlSlugify $seoUrlSlugify)
    {
    }

    /**
     * @throws InconsistentCriteriaIdsException
     */
    public function save(string $languageId, ?string $salesChannelId, string $referenceId, string $routeName, string $seoPathInfo, bool $isCanonical = true): ?SeoUrlEntity
    {
        /** Escape the url */
        $seoPathInfo = $this->seoUrlSlugify->convert($seoPathInfo);

        /** Check for unique */
        $uniqueEntity = $this->seoUrlRepository->getBySeoPathInfo($languageId, $salesChannelId, $seoPathInfo);

        if(empty($seoPathInfo)) {
            return null;
        }

        /** Reset canonical flag, if the current url should be the new canonical */
        if (true === $isCanonical) {
            $this->seoUrlRepository->resetCanonicalFlag($languageId, $salesChannelId, $referenceId, $routeName);
        }

        /** Check if there is an unique entry */
        if (null !== $uniqueEntity) {
            /** Run an update instead a create, if the entry is already set */
            if ($uniqueEntity->getForeignKey() === $referenceId && $uniqueEntity->getRouteName() === $routeName) {
                $this->seoUrlRepository->upsert([
                    [
                        /**
                         * isModified:
                         * We make sure the isModified flag is set when the isCanonical flag is set.
                         * Otherwise this entry would be overwritten by the shopware url generator.
                         */
                        'id' => $uniqueEntity->getId(),
                        'isCanonical' => $isCanonical,
                        'isModified' => $isCanonical
                    ]
                ]);

                return $this->seoUrlRepository->get($uniqueEntity->getId());
            }

            /** Run again, with an postfix, because the seo url is already registered */
            return $this->save(
                $languageId,
                $salesChannelId,
                $referenceId,
                $routeName,
                $this->addSeoPathInfoPostfix($seoPathInfo, $referenceId),
                $isCanonical
            );
        }



        /** Save the url */
        $seoUrlId = Uuid::randomHex();
        $this->seoUrlRepository->upsert([
            [
                'id' => $seoUrlId,
                'languageId' => $languageId,
                'salesChannelId' => $salesChannelId,
                'foreignKey' => $referenceId,
                'routeName' => $routeName,
                'pathInfo' => SeoUrlRepository::PATH_INFO_PREFIXES[$routeName] . $referenceId,
                'seoPathInfo' => $seoPathInfo,
                'isCanonical' => $isCanonical,
                'isModified' => true
            ]
        ]);

        return $this->seoUrlRepository->get($seoUrlId);
    }

    private function addSeoPathInfoPostfix(string $seoPathInfo, string $referenceId): string
    {
        return sprintf(
            '%s-%s',
            $seoPathInfo,
            $referenceId
        );
    }
}
