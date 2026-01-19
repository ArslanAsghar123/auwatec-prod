import template from './sw-cms-el-cbax-lexicon-entry.html.twig';
import './sw-cms-el-cbax-lexicon-entry.scss';

const { Component, Mixin } = Shopware;

Component.register('sw-cms-el-cbax-lexicon-entry', {
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
            this.initElementConfig('cbax-lexicon-entry');
        }
    }
});
