<?php declare(strict_types=1);

namespace Cogi\CogiFooterKit\Core\Content\Aggregate\FooterKitTranslation;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                add(FooterKitTranslationEntity $entity)
 * @method void                set(string $key, FooterKitTranslationEntity $entity)
 * @method FooterKitTranslationEntity[]    getIterator()
 * @method FooterKitTranslationEntity[]    getElements()
 * @method FooterKitTranslationEntity|null get(string $key)
 * @method FooterKitTranslationEntity|null first()
 * @method FooterKitTranslationEntity|null last()
 */
class FooterKitTranslationCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return FooterKitTranslationEntity::class;
    }
}