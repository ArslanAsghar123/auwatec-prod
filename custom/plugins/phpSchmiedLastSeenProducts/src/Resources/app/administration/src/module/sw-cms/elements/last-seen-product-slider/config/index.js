import template from './sw-cms-el-config-last-seen-product-slider.html.twig';

const { Component, Mixin } = Shopware;
const { Criteria, EntityCollection } = Shopware.Data;

Component.register('sw-cms-el-config-last-seen-product-slider', {
    template,

    mixins: [
        Mixin.getByName('cms-element')
    ],

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('last-seen-product-slider');
        }
    }
});
