<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Seo\SeoDataFetcher\Common;

use DreiscSeoPro\Core\Foundation\Context\ContextFactory\Struct\ContextStruct;
use Doctrine\DBAL\DBALException;
use DreiscSeoPro\Core\Content\Category\CategoryEnum;
use DreiscSeoPro\Core\Content\Media\MediaRepository;
use DreiscSeoPro\Core\Content\Product\ProductEnum;
use DreiscSeoPro\Core\Content\SeoUrl\SeoUrlRepository;
use DreiscSeoPro\Core\Foundation\Context\ContextFactory;
use DreiscSeoPro\Core\Foundation\Context\LanguageChainFactory;
use DreiscSeoPro\Core\Foundation\Dal\EntityRepository;
use DreiscSeoPro\Core\Seo\SeoDataFetcher\Struct\SeoDataFetchResultStruct;
use Shopware\Core\Content\LandingPage\LandingPageEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\Seo\SeoUrl\SeoUrlEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Uuid\Exception\InvalidUuidException;
use Shopware\Core\Framework\Uuid\Uuid;

class DataFetcher
{
    /**
     * @var MediaRepository
     */
    private $mediaRepository;

    /**
     * @param MediaRepository $mediaRepository
     */
    public function __construct(private readonly LanguageChainFactory $languageChainFactory, private readonly ContextFactory $contextFactory, private readonly SeoUrlRepository $seoUrlRepository, MediaRepository $mediaRepository)
    {
        $this->mediaRepository = $mediaRepository;
    }

    public function fetchBaseInformation(Entity $entity, string $referenceId, string $languageId, ?string $salesChannelId, ?SeoDataFetchResultStruct $parentSeoDataFetchResultStruct, string $seoUrlRouteName): SeoDataFetchResultStruct
    {
        $seoDataFetchResultStruct = new SeoDataFetchResultStruct();

        /** Get translated fields */
        $translated = $entity->getTranslated();

        /** Get custom fields and translated custom fields */
        $customFields = $entity->getCustomFields();
        $translatedCustomFields = !empty($translated['customFields']) ? $translated['customFields'] : [];

        /** Load the url entity */
        $urlEntity = $this->fetchUrlEntity(
            $referenceId,
            $languageId,
            $salesChannelId,
            $seoUrlRouteName
        );

        /** Load the inherit url entity */
        $inheritUrlEntity = $this->fetchUrlEntity(
            $referenceId,
            $languageId,
            null,
            $seoUrlRouteName
        );

        /** Collect meta title information */
        $this->collectMetaTitleInfo(
            $seoDataFetchResultStruct,
            $entity,
            $translated
        );

        /** Collect meta description information */
        $this->collectMetaDescriptionInfo(
            $seoDataFetchResultStruct,
            $entity,
            $translated
        );

        /** Collect url information */
        $this->collectUrlInfo(
            $seoDataFetchResultStruct,
            $urlEntity,
            $inheritUrlEntity
        );

        /** Collect robots tag information */
        $this->collectRobotsTagInfo(
            $seoDataFetchResultStruct,
            $customFields,
            $translatedCustomFields,
            CategoryEnum::CUSTOM_FIELD__DREISC_SEO_ROBOTS_TAG,
            $parentSeoDataFetchResultStruct
        );

        /** Collect robots tag information */
        $this->collectCanonicalLinkInfo(
            $seoDataFetchResultStruct,
            $translatedCustomFields,
            $salesChannelId,
            $parentSeoDataFetchResultStruct,
            $entity
        );

        /** Collect robots tag information */
        $this->collectSocialMedia(
            $seoDataFetchResultStruct,
            $customFields,
            $translatedCustomFields,
            $parentSeoDataFetchResultStruct
        );

        return $seoDataFetchResultStruct;
    }

    /**
     * @throws DBALException
     * @throws InconsistentCriteriaIdsException
     * @throws InvalidUuidException
     */
    public function fetchEntity(EntityRepository $entityRepository, string $referenceId, string $languageId, bool $considerInheritance): ?Entity
    {
        /** Create the language id chain */
        $languageIdChain = $this->languageChainFactory->getLanguageIdChain($languageId);

        /** Initialize a context for the given language */
        $context = $this->contextFactory->createContext(
            (new ContextStruct())
                ->setLanguageIdChain($languageIdChain)
                ->setConsiderInheritance($considerInheritance)
        );

        /** Load the entity */
        return $entityRepository->get($referenceId, null, $context);
    }

    public function fetchEntityCollection(EntityRepository $entityRepository, array $referenceIds, string $languageId, bool $considerInheritance)
    {
        /** Create the language id chain */
        $languageIdChain = $this->languageChainFactory->getLanguageIdChain($languageId);

        /** Initialize a context for the given language */
        $context = $this->contextFactory->createContext(
            (new ContextStruct())
                ->setLanguageIdChain($languageIdChain)
                ->setConsiderInheritance($considerInheritance)
        );

        /** Load the entity */
        return $entityRepository->search(new Criteria($referenceIds), $context)->getEntities();
    }

    /**
     * @throws InconsistentCriteriaIdsException
     */
    private function fetchUrlEntity(string $referenceId, string $languageId, ?string $salesChannelId, string $routeName): ?SeoUrlEntity
    {
        $urlEntity = $this->seoUrlRepository->getByContext(
            $languageId,
            $salesChannelId,
            $routeName,
            $referenceId,
            [ SeoUrlRepository::SEO_FILTER__IS_CANONICAL => true ]
        );

        return $urlEntity->first();
    }

    private function collectMetaTitleInfo(SeoDataFetchResultStruct $seoDataFetchResultStruct, Entity $entity, ?array $translated): void
    {
        /** Set defaults */
        $seoDataFetchResultStruct->setIsInheritedMetaTitle(false);

        /** Check if it is an inherit value. This is only possible, if there is another language in the chain */
        if (null === $entity->getMetaTitle() && !empty($translated['metaTitle'])) {
            $seoDataFetchResultStruct->setIsInheritedMetaTitle(true);
        }

        /** Set the value */
        $seoDataFetchResultStruct->setMetaTitle($translated['metaTitle'] ?? null);
    }

    private function collectMetaDescriptionInfo(SeoDataFetchResultStruct $seoDataFetchResultStruct, Entity $entity, array $translated): void
    {
        /** Set defaults */
        $seoDataFetchResultStruct->setIsInheritedMetaDescription(false);

        /** Check if it is an inherit value. This is only possible, if there is another language in the chain */
        if (null === $entity->getMetaDescription() && !empty($translated['metaDescription'])) {
            $seoDataFetchResultStruct->setIsInheritedMetaDescription(true);
        }

        /** Set the value */
        $seoDataFetchResultStruct->setMetaDescription($translated['metaDescription'] ?? null);
    }

    private function collectUrlInfo(SeoDataFetchResultStruct $seoDataFetchResultStruct, ?SeoUrlEntity $urlEntity, ?SeoUrlEntity $inheritUrlEntity): void
    {
        /** Set defaults */
        $seoDataFetchResultStruct->setIsInheritedUrl(false);
        $seoDataFetchResultStruct->setIsModifiedUrl(false);

        /** Check if there is an inherit url, if the url entity is null */
        if (null === $urlEntity && null !== $inheritUrlEntity) {
            $seoDataFetchResultStruct->setIsInheritedUrl(true);
            $url = $inheritUrlEntity->getSeoPathInfo();
        } elseif (null !== $urlEntity) {
            $seoDataFetchResultStruct->setIsModifiedUrl($urlEntity->getIsModified());
            $url = $urlEntity->getSeoPathInfo();
        } else {
            $url = null;
        }

        /** Set the url */
        $seoDataFetchResultStruct->setUrl($url);
    }

    private function collectRobotsTagInfo(SeoDataFetchResultStruct $seoDataFetchResultStruct, ?array $customFields, ?array $translatedCustomFields, string $fieldName, ?SeoDataFetchResultStruct $parentSeoDataFetchResultStruct)
    {
        /** Set defaults */
        $seoDataFetchResultStruct->setIsInheritedRobotsTag(false);

        $robotsTag = null;
        if (null !== $customFields && !empty($customFields[$fieldName])) {
            $robotsTag = $customFields[$fieldName];
        }

        /** Check for inherit value */
        if (null === $robotsTag && !empty($translatedCustomFields[$fieldName])) {
            $seoDataFetchResultStruct->setIsInheritedRobotsTag(true);
        }

        /** Set value */
        $seoDataFetchResultStruct->setRobotsTag(
            $translatedCustomFields[ProductEnum::CUSTOM_FIELD__DREISC_SEO_ROBOTS_TAG] ?? null
        );

        /** Parent fallback, if available */
        if (null === $robotsTag && null !== $parentSeoDataFetchResultStruct) {
            $seoDataFetchResultStruct->setRobotsTag(
                $parentSeoDataFetchResultStruct->getRobotsTag()
            );

            $seoDataFetchResultStruct->setIsInheritedRobotsTag(
                $parentSeoDataFetchResultStruct->isInheritedRobotsTag()
            );
        }
    }
    /**
     * @param array|null $customFields
     * @param string $fieldName
     */
    private function collectCanonicalLinkInfo(SeoDataFetchResultStruct $seoDataFetchResultStruct, ?array $translatedCustomFields, ?string $salesChannelId, ?SeoDataFetchResultStruct $parentSeoDataFetchResultStruct, Entity $entity): void
    {
        if (null === $salesChannelId) {
            return;
        }

        if ($entity instanceof LandingPageEntity) {
            if(!empty($translatedCustomFields)) {
                if(!empty($translatedCustomFields['dreisc_seo_canonical_link_type'])) {
                    $seoDataFetchResultStruct->setCanonicalLinkType($translatedCustomFields['dreisc_seo_canonical_link_type']);
                }

                if(!empty($translatedCustomFields['dreisc_seo_canonical_link_reference'])) {
                    $seoDataFetchResultStruct->setCanonicalLinkReference($translatedCustomFields['dreisc_seo_canonical_link_reference']);
                }
            }

            return;
        }

        if(!empty($translatedCustomFields)) {
            if (!empty($translatedCustomFields['dreisc_seo_canonical_link_type']) && !empty($translatedCustomFields['dreisc_seo_canonical_link_type'][$salesChannelId])) {
                /** Deprecated way */
                $seoDataFetchResultStruct->setCanonicalLinkType($translatedCustomFields['dreisc_seo_canonical_link_type'][$salesChannelId]);
            } elseif (!empty($translatedCustomFields['dreisc_seo_canonical_link_type']) && !is_array($translatedCustomFields['dreisc_seo_canonical_link_type'])) {
                $decodedCanonicalLinkTypes = json_decode((string) $translatedCustomFields['dreisc_seo_canonical_link_type'], true);
                if (is_array($decodedCanonicalLinkTypes)) {
                    foreach($decodedCanonicalLinkTypes as $decodedCanonicalLinkType) {
                        if(!empty($decodedCanonicalLinkType['salesChannelId']) && $decodedCanonicalLinkType['salesChannelId'] === $salesChannelId) {
                            $seoDataFetchResultStruct->setCanonicalLinkType($decodedCanonicalLinkType['type']);
                        }
                    }
                }
            }

            if (!empty($translatedCustomFields['dreisc_seo_canonical_link_reference']) && !empty($translatedCustomFields['dreisc_seo_canonical_link_reference'][$salesChannelId])) {
                /** Deprecated way */
                $seoDataFetchResultStruct->setCanonicalLinkReference($translatedCustomFields['dreisc_seo_canonical_link_reference'][$salesChannelId]);
            } elseif (!empty($translatedCustomFields['dreisc_seo_canonical_link_reference']) && !is_array($translatedCustomFields['dreisc_seo_canonical_link_reference'])) {
                $decodedCanonicalLinkReferences = json_decode((string) $translatedCustomFields['dreisc_seo_canonical_link_reference'], true);
                if (is_array($decodedCanonicalLinkReferences)) {
                    foreach($decodedCanonicalLinkReferences as $decodedCanonicalLinkReference) {
                        if(!empty($decodedCanonicalLinkReference['salesChannelId']) && $decodedCanonicalLinkReference['salesChannelId'] === $salesChannelId) {
                            $seoDataFetchResultStruct->setCanonicalLinkReference($decodedCanonicalLinkReference['reference']);
                        }
                    }
                }
            }
        }

        /** Parent fallback, if available */
        if (null !== $parentSeoDataFetchResultStruct && null === $seoDataFetchResultStruct->getCanonicalLinkType()) {
            $seoDataFetchResultStruct->setCanonicalLinkType(
                $parentSeoDataFetchResultStruct->getCanonicalLinkType()
            );

            $seoDataFetchResultStruct->setCanonicalLinkReference(
                $parentSeoDataFetchResultStruct->getCanonicalLinkReference()
            );
        }
    }

    private function collectSocialMedia(SeoDataFetchResultStruct $seoDataFetchResultStruct, $customFields, array $translatedCustomFields, ?SeoDataFetchResultStruct $parentSeoDataFetchResultStruct)
    {
        $this->collectFacebookTitle(
            $seoDataFetchResultStruct,
            $customFields,
            $translatedCustomFields,
            $parentSeoDataFetchResultStruct
        );

        $this->collectFacebookDescription(
            $seoDataFetchResultStruct,
            $customFields,
            $translatedCustomFields,
            $parentSeoDataFetchResultStruct
        );

        $this->collectFacebookImage(
            $seoDataFetchResultStruct,
            $customFields,
            $translatedCustomFields,
            $parentSeoDataFetchResultStruct
        );

        $this->collectTwitterTitle(
            $seoDataFetchResultStruct,
            $customFields,
            $translatedCustomFields,
            $parentSeoDataFetchResultStruct
        );

        $this->collectTwitterDescription(
            $seoDataFetchResultStruct,
            $customFields,
            $translatedCustomFields,
            $parentSeoDataFetchResultStruct
        );

        $this->collectTwitterImage(
            $seoDataFetchResultStruct,
            $customFields,
            $translatedCustomFields,
            $parentSeoDataFetchResultStruct
        );
    }

    private function collectFacebookTitle(SeoDataFetchResultStruct $seoDataFetchResultStruct, $customFields, array $translatedCustomFields, ?SeoDataFetchResultStruct $parentSeoDataFetchResultStruct): void
    {
        $fieldName = ProductEnum::CUSTOM_FIELD__DREISC_SEO_FACEBOOK_TITLE;

        /** Set defaults */
        $seoDataFetchResultStruct->setIsInheritedFacebookTitle(false);

        $facebookTitle = null;
        if (null !== $customFields && !empty($customFields[$fieldName])) {
            $facebookTitle = $customFields[$fieldName];
        }

        /** Check for inherit value */
        if (null === $facebookTitle && !empty($translatedCustomFields[$fieldName])) {
            $seoDataFetchResultStruct->setIsInheritedFacebookTitle(true);
        }

        /** Set value */
        $seoDataFetchResultStruct->setFacebookTitle(
            $translatedCustomFields[$fieldName] ?? null
        );

        /** Parent fallback, if available */
        if (null === $facebookTitle && null !== $parentSeoDataFetchResultStruct) {
            $seoDataFetchResultStruct->setFacebookTitle(
                $parentSeoDataFetchResultStruct->getFacebookTitle()
            );

            $seoDataFetchResultStruct->setIsInheritedFacebookTitle(
                $parentSeoDataFetchResultStruct->isInheritedFacebookTitle()
            );
        }
    }

    private function collectFacebookDescription(SeoDataFetchResultStruct $seoDataFetchResultStruct, $customFields, array $translatedCustomFields, ?SeoDataFetchResultStruct $parentSeoDataFetchResultStruct): void
    {
        $fieldName = ProductEnum::CUSTOM_FIELD__DREISC_SEO_FACEBOOK_DESCRIPTION;

        /** Set defaults */
        $seoDataFetchResultStruct->setIsInheritedFacebookDescription(false);

        $facebookDescription = null;
        if (null !== $customFields && !empty($customFields[$fieldName])) {
            $facebookDescription = $customFields[$fieldName];
        }

        /** Check for inherit value */
        if (null === $facebookDescription && !empty($translatedCustomFields[$fieldName])) {
            $seoDataFetchResultStruct->setIsInheritedFacebookDescription(true);
        }

        /** Set value */
        $seoDataFetchResultStruct->setFacebookDescription(
            $translatedCustomFields[$fieldName] ?? null
        );

        /** Parent fallback, if available */
        if (null === $facebookDescription && null !== $parentSeoDataFetchResultStruct) {
            $seoDataFetchResultStruct->setFacebookDescription(
                $parentSeoDataFetchResultStruct->getFacebookDescription()
            );

            $seoDataFetchResultStruct->setIsInheritedFacebookDescription(
                $parentSeoDataFetchResultStruct->isInheritedFacebookDescription()
            );
        }
    }

    /**
     * @param $customFields
     * @throws InconsistentCriteriaIdsException
     */
    private function collectFacebookImage(SeoDataFetchResultStruct $seoDataFetchResultStruct, $customFields, array $translatedCustomFields, ?SeoDataFetchResultStruct $parentSeoDataFetchResultStruct): void
    {
        $fieldName = ProductEnum::CUSTOM_FIELD__DREISC_SEO_FACEBOOK_IMAGE;

        $facebookImage = null;
        if (null !== $customFields && !empty($customFields[$fieldName])) {
            $facebookImage = $customFields[$fieldName];
        }

        /** Set value */
        $seoDataFetchResultStruct->setFacebookImage(
            $translatedCustomFields[$fieldName] ?? null
        );

        /** Parent fallback, if available */
        if (null === $facebookImage && null !== $parentSeoDataFetchResultStruct) {
            $seoDataFetchResultStruct->setFacebookImage(
                $parentSeoDataFetchResultStruct->getFacebookImage()
            );
        }

        /** Fetch the image url */
        if(null !== $seoDataFetchResultStruct->getFacebookImage() && Uuid::isValid($seoDataFetchResultStruct->getFacebookImage())) {
            $mediaEntity = $this->mediaRepository->get($seoDataFetchResultStruct->getFacebookImage());
            if (null !== $mediaEntity) {
                $seoDataFetchResultStruct->setFacebookImage(
                    $mediaEntity->getUrl()
                );
            }
        }
    }

    private function collectTwitterTitle(SeoDataFetchResultStruct $seoDataFetchResultStruct, $customFields, array $translatedCustomFields, ?SeoDataFetchResultStruct $parentSeoDataFetchResultStruct): void
    {
        $fieldName = ProductEnum::CUSTOM_FIELD__DREISC_SEO_TWITTER_TITLE;

        /** Set defaults */
        $seoDataFetchResultStruct->setIsInheritedTwitterTitle(false);

        $twitterTitle = null;
        if (null !== $customFields && !empty($customFields[$fieldName])) {
            $twitterTitle = $customFields[$fieldName];
        }

        /** Check for inherit value */
        if (null === $twitterTitle && !empty($translatedCustomFields[$fieldName])) {
            $seoDataFetchResultStruct->setIsInheritedTwitterTitle(true);
        }

        /** Set value */
        $seoDataFetchResultStruct->setTwitterTitle(
            $translatedCustomFields[$fieldName] ?? null
        );

        /** Parent fallback, if available */
        if (null === $twitterTitle && null !== $parentSeoDataFetchResultStruct) {
            $seoDataFetchResultStruct->setTwitterTitle(
                $parentSeoDataFetchResultStruct->getTwitterTitle()
            );

            $seoDataFetchResultStruct->setIsInheritedTwitterTitle(
                $parentSeoDataFetchResultStruct->isInheritedTwitterTitle()
            );
        }
    }

    private function collectTwitterDescription(SeoDataFetchResultStruct $seoDataFetchResultStruct, $customFields, array $translatedCustomFields, ?SeoDataFetchResultStruct $parentSeoDataFetchResultStruct): void
    {
        $fieldName = ProductEnum::CUSTOM_FIELD__DREISC_SEO_TWITTER_DESCRIPTION;

        /** Set defaults */
        $seoDataFetchResultStruct->setIsInheritedTwitterDescription(false);

        $twitterDescription = null;
        if (null !== $customFields && !empty($customFields[$fieldName])) {
            $twitterDescription = $customFields[$fieldName];
        }

        /** Check for inherit value */
        if (null === $twitterDescription && !empty($translatedCustomFields[$fieldName])) {
            $seoDataFetchResultStruct->setIsInheritedTwitterDescription(true);
        }

        /** Set value */
        $seoDataFetchResultStruct->setTwitterDescription(
            $translatedCustomFields[$fieldName] ?? null
        );

        /** Parent fallback, if available */
        if (null === $twitterDescription && null !== $parentSeoDataFetchResultStruct) {
            $seoDataFetchResultStruct->setTwitterDescription(
                $parentSeoDataFetchResultStruct->getTwitterDescription()
            );

            $seoDataFetchResultStruct->setIsInheritedTwitterDescription(
                $parentSeoDataFetchResultStruct->isInheritedTwitterDescription()
            );
        }
    }

    private function collectTwitterImage(SeoDataFetchResultStruct $seoDataFetchResultStruct, $customFields, array $translatedCustomFields, ?SeoDataFetchResultStruct $parentSeoDataFetchResultStruct): void
    {
        $fieldName = ProductEnum::CUSTOM_FIELD__DREISC_SEO_TWITTER_IMAGE;

        $twitterImage = null;
        if (null !== $customFields && !empty($customFields[$fieldName])) {
            $twitterImage = $customFields[$fieldName];
        }

        /** Set value */
        $seoDataFetchResultStruct->setTwitterImage(
            $translatedCustomFields[$fieldName] ?? null
        );

        /** Parent fallback, if available */
        if (null === $twitterImage && null !== $parentSeoDataFetchResultStruct) {
            $seoDataFetchResultStruct->setTwitterImage(
                $parentSeoDataFetchResultStruct->getTwitterImage()
            );
        }

        /** Fetch the image url */
        if(null !== $seoDataFetchResultStruct->getTwitterImage() && Uuid::isValid($seoDataFetchResultStruct->getTwitterImage())) {
            $mediaEntity = $this->mediaRepository->get($seoDataFetchResultStruct->getTwitterImage());
            if (null !== $mediaEntity) {
                $seoDataFetchResultStruct->setTwitterImage(
                    $mediaEntity->getUrl()
                );
            }
        }
    }
}
