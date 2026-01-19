import template from './cbax-lexicon-cmspage-single-select.html.twig';

const { Component } = Shopware;
const { Criteria } = Shopware.Data;

Component.extend('cbax-lexicon-cmspage-single-select', 'sw-entity-single-select', {
    template,

    methods: {
        createdComponent() {
            this.criteria.addFilter(Criteria.equals('type', 'cbax_lexicon'));
            this.loadSelected();
        }
    }
});
