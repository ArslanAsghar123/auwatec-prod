import template from './cbax-lexicon-entity-visibility.html.twig';

const { Component } = Shopware;
const { EntityCollection } = Shopware.Data;

Component.extend('cbax-lexicon-entity-visibility', 'sw-entity-multi-select', {
    template,

    computed: {
        repository() {
            return this.repositoryFactory.create('sales_channel');
        },
        associationRepository() {
            return this.repositoryFactory.create('cbax_lexicon_sales_channel');
        }
    },

    methods: {
        isSelected(item) {
            return this.currentCollection.some(entity => {
                return entity.salesChannelId === item.id;
            });
        },

        addItem(item) {
            // Remove when already selected
            if (this.isSelected(item)) {
                const associationEntity = this.currentCollection.find(entity => {
                    return entity.salesChannelId === item.id;
                });
                this.remove(associationEntity);
                return;
            }

            // Create new entity
            const newSalesChannelAssociation = this.associationRepository.create(this.entityCollection.context);

            newSalesChannelAssociation.salesChannelId = item.id;
            newSalesChannelAssociation.salesChannel = item;

            this.$emit('item-add', item);

            const changedCollection = EntityCollection.fromCollection(this.currentCollection);
            changedCollection.add(newSalesChannelAssociation);

            this.emitChanges(changedCollection);
            this.onSelectExpanded();
        }
    }
});
