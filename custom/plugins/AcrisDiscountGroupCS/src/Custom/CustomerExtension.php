<?php declare(strict_types=1);

namespace Acris\DiscountGroup\Custom;

use Shopware\Core\Checkout\Customer\CustomerDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class CustomerExtension extends EntityExtension
{
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new OneToManyAssociationField('acrisDiscountGroups',
                DiscountGroupDefinition::class, 'customer_id'))
                ->addFlags(new CascadeDelete())
        );
    }

    public function getDefinitionClass(): string
    {
        return CustomerDefinition::class;
    }
}
