<?php declare(strict_types=1);

namespace Cogi\CogiFooterKit\Core\Content;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void              add(FooterKitEntity $entity)
 * @method void              set(string $key, FooterKitEntity $entity)
 * @method FooterKitEntity[]    getIterator()
 * @method FooterKitEntity[]    getElements()
 * @method FooterKitEntity|null get(string $key)
 * @method FooterKitEntity|null first()
 * @method FooterKitEntity|null last()
 */
class FooterKitCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return FooterKitEntity::class;
    }
}