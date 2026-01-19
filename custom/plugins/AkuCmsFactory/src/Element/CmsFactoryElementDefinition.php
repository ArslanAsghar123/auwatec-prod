<?php declare(strict_types=1);

namespace AkuCmsFactory\Element;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\AllowHtml;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\NoConstraint;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Extension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\RuleAreas;

class CmsFactoryElementDefinition extends EntityDefinition {
    public const ENTITY_NAME = 'cms_factory_element';

    public function getEntityName(): string {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string {
        return CmsFactoryElementCollection::class;
    }

    public function getEntityClass(): string {
        return CmsFactoryElement::class;
    }

    protected function defineFields(): FieldCollection {
        // twig !isSanitized setzen, sonst werden '{{ }}' in einer URL escaped
        // tönt zwar unlogisch, hat aber den gewünschten Effekt
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            new StringField('name', 'name'),

            (new LongTextField('twig', 'twig'))
                ->addFlags(new AllowHtml(false))
            , new LongTextField('fields', 'fields'),

        ]);
    }
}