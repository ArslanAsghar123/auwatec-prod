<?php declare(strict_types=1);

namespace Acris\Gpsr\Custom;

use Acris\Gpsr\Custom\Aggregate\GpsrContact\GpsrContactRuleDefinition;
use Acris\Gpsr\Custom\Aggregate\GpsrManufacturer\GpsrManufacturerRuleDefinition;
use Acris\Gpsr\Custom\Aggregate\GpsrNote\GpsrNoteRuleDefinition;
use Shopware\Core\Content\Rule\RuleDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class RuleExtension extends EntityExtension
{
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new ManyToManyAssociationField(
                'acrisGpsrNotes',
                GpsrNoteDefinition::class,
                GpsrNoteRuleDefinition::class,
                'rule_id',
                'gpsr_note_id'
            ))->addFlags(new ApiAware())
        );

        $collection->add(
            (new ManyToManyAssociationField(
                'acrisGpsrManufacturers',
                GpsrManufacturerDefinition::class,
                GpsrManufacturerRuleDefinition::class,
                'rule_id',
                'gpsr_manufacturer_id'
            ))->addFlags(new ApiAware())
        );

        $collection->add(
            (new ManyToManyAssociationField(
                'acrisGpsrContacts',
                GpsrContactDefinition::class,
                GpsrContactRuleDefinition::class,
                'rule_id',
                'gpsr_contact_id'
            ))->addFlags(new ApiAware())
        );
    }

    public function getDefinitionClass(): string
    {
        return RuleDefinition::class;
    }
}
