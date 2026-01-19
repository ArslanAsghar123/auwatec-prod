import template from './config.html.twig';

const { Component, Mixin } = Shopware;
const { Criteria, EntityCollection } = Shopware.Data;

Component.register('doofinder-element-config', {
    template,

    mixins: [
        Mixin.getByName('cms-element')
    ],

    inject: [
        'repositoryFactory',
        'context'
    ],

    data() {
    },

    computed: {
        totalProducts: {
            get() { return this.element.config.totalProducts.value; },
            set(value) { this.element.config.totalProducts.value = value; }
        },
        title: {
            get() { return this.element.config.title.value; },
            set(value) { this.element.config.title.value = value; }
        },
    },

    created() {
        this.initElementConfig('intedia-doofinder-recommendation-element');
    },

    methods: {
    },
});
