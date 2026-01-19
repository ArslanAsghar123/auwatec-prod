import template from './sw-cms-el-cbax-lexicon-navigation.html.twig';
import './sw-cms-el-cbax-lexicon-navigation.scss';

const { Component, Mixin } = Shopware;

Component.register('sw-cms-el-cbax-lexicon-navigation', {
    template,

    mixins: [
        Mixin.getByName('cms-element')
    ],

    computed: {
        isCbaxLexiconCmsPage() {
            return this.cmsPageState.currentPage.type === 'cbax_lexicon';
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('cbax-lexicon-navigation');
        }
    }
});
