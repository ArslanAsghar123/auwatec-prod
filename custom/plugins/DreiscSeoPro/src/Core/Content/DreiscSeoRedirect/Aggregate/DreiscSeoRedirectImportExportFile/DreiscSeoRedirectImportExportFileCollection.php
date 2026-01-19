<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Content\DreiscSeoRedirect\Aggregate\DreiscSeoRedirectImportExportFile;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
* @method void                add(DreiscSeoRedirectImportExportFileEntity $entity)
* @method void                set(string $key, DreiscSeoRedirectImportExportFileEntity $entity)
* @method DreiscSeoRedirectImportExportFileEntity[]    getIterator()
* @method DreiscSeoRedirectImportExportFileEntity[]    getElements()
* @method DreiscSeoRedirectImportExportFileEntity|null get(string $key)
* @method DreiscSeoRedirectImportExportFileEntity|null first()
* @method DreiscSeoRedirectImportExportFileEntity|null last()
*/
class DreiscSeoRedirectImportExportFileCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return DreiscSeoRedirectImportExportFileEntity::class;
    }
}

