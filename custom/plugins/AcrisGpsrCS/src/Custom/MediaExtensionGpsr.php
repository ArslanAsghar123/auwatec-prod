<?php declare(strict_types=1);

namespace Acris\Gpsr\Custom;

use Shopware\Core\Content\Media\MediaDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Inherited;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class MediaExtensionGpsr extends EntityExtension
{
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new OneToManyAssociationField(
                'acrisGpsrDownloads',
                ProductGpsrDownloadDefinition::class,
                'media_id',
                'id')
            )->addFlags(new CascadeDelete(), new ApiAware())
        );

        $collection->add(
            (new OneToManyAssociationField(
                'acrisGpsrDownloads',
                ProductGpsrDownloadDefinition::class,
                'preview_media_id',
                'id')
            )->addFlags(new CascadeDelete(), new ApiAware())
        );

        $collection->add(
            (new OneToManyAssociationField(
                'acrisGpsrContactDownloads',
                GpsrContactDownloadDefinition::class,
                'media_id',
                'id')
            )->addFlags(new CascadeDelete(), new ApiAware())
        );

        $collection->add(
            (new OneToManyAssociationField(
                'acrisGpsrContactDownloads',
                GpsrContactDownloadDefinition::class,
                'preview_media_id',
                'id')
            )->addFlags(new CascadeDelete(), new ApiAware())
        );

        $collection->add(
            (new OneToManyAssociationField(
                'acrisGpsrNoteDownloads',
                GpsrNoteDownloadDefinition::class,
                'media_id',
                'id')
            )->addFlags(new CascadeDelete(), new ApiAware())
        );

        $collection->add(
            (new OneToManyAssociationField(
                'acrisGpsrNoteDownloads',
                GpsrNoteDownloadDefinition::class,
                'preview_media_id',
                'id')
            )->addFlags(new CascadeDelete(), new ApiAware())
        );
    }

    public function getDefinitionClass(): string
    {
        return MediaDefinition::class;
    }
}
