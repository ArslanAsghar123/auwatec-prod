<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Foundation\Translation;

use DreiscSeoPro\Core\Foundation\DemoData\DemoDataIds;
use DreiscSeoPro\Core\Foundation\Translation\Exception\EntityHasNoTranslationsException;
use DreiscSeoPro\Core\Foundation\Translation\Exception\UnknownPropertyNameException;
use DreiscSeoPro\Core\Foundation\Translation\Exception\UnloadedTranslationAssociaionException;
use Shopware\Core\Content\Category\Aggregate\CategoryTranslation\CategoryTranslationCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;

class EntityInheritanceFieldReader
{
    /**
     * @see \DreiscSeo\Core\Foundation\Context\ContextFactory for language chain loading
     *
     * Loads the translated field for a special language or the fallback value if null
     *
     * @example
     *  $categoryEntity = $this->categoryRepository->get(
     *      DemoDataIds::CATEGORY__MAIN__PRODUCTS__MAIN_PRODUCTS,
     *      [ 'translations' ]
     *  );
     *
     *  $translatedName = $this->entityInheritanceFieldReader->get($categoryEntity, $this->languageDeId, 'name');
     *
     * @param string $languageId
     * @param string $property
     * @return mixed
     * @throws EntityHasNoTranslationsException
     * @throws UnknownPropertyNameException
     * @throws UnloadedTranslationAssociaionException
     */
    public function get(Entity $entity, string $languageId, string $property)
    {
        /** Check if the property exists */
        if (!$entity->has($property)) {
            throw new UnknownPropertyNameException($property);
        }

        /** Fallback value of the default context */
        $fallbackValue = $entity->get($property);

        /** Check, if the entity has translations */
        if (!method_exists($entity, 'getTranslations')) {
            throw new EntityHasNoTranslationsException($entity::class);
        }

        /** Fetch the translations */
        /** @var CategoryTranslationCollection $translations */
        $translations = $entity->getTranslations();

        /** Check, if the translation association was loaded */
        if (null === $translations) {
            throw new UnloadedTranslationAssociaionException($entity::class);
        };

        /** Fetch the translations for the given language */
        $translations = $translations->filterByLanguageId($languageId);
        $languageTranslations = $translations->first();

        /** Returns fallback, if no translation is set for this language */
        if (null === $languageTranslations) {
            return $fallbackValue;
        }

        /** Fetch the translated property */
        $translatedProperty = $languageTranslations->get($property);

        /** Return the translated property if this not null */
        if (null !== $translatedProperty) {
            return $translatedProperty;
        }

        /** Otherwise we return the fallback */
        return $fallbackValue;
    }
}
