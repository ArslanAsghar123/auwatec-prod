import template from './cbax-lexicon-customfields-multi-select.html.twig';

const { Component } = Shopware;
const { Criteria } = Shopware.Data;

Component.extend('cbax-lexicon-customfields-multi-select', 'sw-entity-multi-id-select', {
    template,

    inject: [
        'repositoryFactory'
    ],

    props: {
        repository: {
            type: Object,
            required: false,
            default() {
                return this.repositoryFactory.create('custom_field');
            },
        }
    },

    methods: {
        createdComponent() {
            //this.repository = ... does not work here, this would work:
            //this._.props.repository = this.repositoryFactory.create('custom_field');
            //but is not needed because of props default

            this.criteria.addAssociation('customFieldSet');
            this.criteria.getAssociation('customFieldSet').addAssociation('relations');
            this.criteria.addFilter(Criteria.equals('customFieldSet.relations.entityName', 'product'));
            this.criteria.addFilter(Criteria.equalsAny('type', ['text', 'html']));

            this.$super('createdComponent');
        }
    }

});
