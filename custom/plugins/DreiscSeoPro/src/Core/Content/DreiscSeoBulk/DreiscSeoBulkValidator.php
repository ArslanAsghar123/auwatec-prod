<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Content\DreiscSeoBulk;

use DreiscSeoPro\Core\Foundation\Dal\Validator;

class DreiscSeoBulkValidator extends Validator
{
    protected function getDefinitionClass(): string
    {
        return DreiscSeoBulkDefinition::class;
    }

    protected function getEntityName(): string
    {
        return DreiscSeoBulkDefinition::ENTITY_NAME;
    }

    protected function fetchViolations($command): void
    {
        /** Check if the area is valid */
        $this->violationIfValueNotOnWhitelist(
            DreiscSeoBulkEntity::AREA__STORAGE_NAME,
            DreiscSeoBulkEnum::VALID_AREAS
        );

        /** Check if the seo option is valid */
        $this->violationIfValueNotOnWhitelist(
            DreiscSeoBulkEntity::SEO_OPTION__STORAGE_NAME,
            DreiscSeoBulkEnum::VALID_SEO_OPTIONS
        );

        $this->violationIfThereIsAlreadyAnEntityWithTheSameValues(
            $command,
            [
                DreiscSeoBulkEntity::SEO_OPTION__STORAGE_NAME,
                DreiscSeoBulkEntity::AREA__STORAGE_NAME,
                DreiscSeoBulkEntity::CATEGORY_ID__STORAGE_NAME,
                DreiscSeoBulkEntity::SALES_CHANNEL_ID__STORAGE_NAME,
                DreiscSeoBulkEntity::LANGUAGE_ID__STORAGE_NAME
            ]
        );

        /** Make sure that a sales channel id is set if "url" is defined as the seo option */
        $this->violationIfFieldHasSpecialValueAndOtherFieldsAreEmpty(
            DreiscSeoBulkEntity::SEO_OPTION__STORAGE_NAME,
            DreiscSeoBulkEnum::SEO_OPTION__URL,
            [
                DreiscSeoBulkEntity::SALES_CHANNEL_ID__STORAGE_NAME
            ]
        );
    }
}
