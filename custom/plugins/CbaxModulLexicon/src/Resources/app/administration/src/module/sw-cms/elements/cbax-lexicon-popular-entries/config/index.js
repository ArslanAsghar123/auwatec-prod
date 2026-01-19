import template from './sw-cms-el-config-cbax-lexicon-popular-entries.html.twig';
import './sw-cms-el-config-cbax-lexicon-popular-entries.scss';

const { Component, Mixin } = Shopware;

Component.register('sw-cms-el-config-cbax-lexicon-popular-entries', {
    template,

    mixins: [
        Mixin.getByName('cms-element')
    ],

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('cbax-lexicon-popular-entries');
        }
    }
});
