<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Content\DreiscSeoBulk\Aggregate\DreiscSeoBulkTemplate;

use DreiscSeoPro\Core\Foundation\Dal\Validator;

class DreiscSeoBulkTemplateValidator extends Validator
{
    protected function getDefinitionClass(): string
    {
        return DreiscSeoBulkTemplateDefinition::class;
    }

    protected function getEntityName(): string
    {
        return DreiscSeoBulkTemplateDefinition::ENTITY_NAME;
    }

    protected function fetchViolations($command): void
    {
        /** Check if the area is valid */
        $this->violationIfValueNotOnWhitelist(
            DreiscSeoBulkTemplateEntity::AREA__STORAGE_NAME,
            DreiscSeoBulkTemplateEnum::VALID_AREAS
        );

        /** Check if the seo option is valid */
        $this->violationIfValueNotOnWhitelist(
            DreiscSeoBulkTemplateEntity::SEO_OPTION__STORAGE_NAME,
            DreiscSeoBulkTemplateEnum::VALID_SEO_OPTIONS
        );
    }
}
