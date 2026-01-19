<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Content\DreiscSeoRedirect\Aggregate\DreiscSeoRedirectImportExportLog;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
* @method void                add(DreiscSeoRedirectImportExportLogEntity $entity)
* @method void                set(string $key, DreiscSeoRedirectImportExportLogEntity $entity)
* @method DreiscSeoRedirectImportExportLogEntity[]    getIterator()
* @method DreiscSeoRedirectImportExportLogEntity[]    getElements()
* @method DreiscSeoRedirectImportExportLogEntity|null get(string $key)
* @method DreiscSeoRedirectImportExportLogEntity|null first()
* @method DreiscSeoRedirectImportExportLogEntity|null last()
*/
class DreiscSeoRedirectImportExportLogCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return DreiscSeoRedirectImportExportLogEntity::class;
    }
}

