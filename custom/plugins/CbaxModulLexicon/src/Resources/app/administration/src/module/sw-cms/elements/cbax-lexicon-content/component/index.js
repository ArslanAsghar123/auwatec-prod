import template from './sw-cms-el-cbax-lexicon-content.html.twig';
import './sw-cms-el-cbax-lexicon-content.scss';

const { Component, Mixin } = Shopware;

Component.register('sw-cms-el-cbax-lexicon-content', {
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
            this.initElementConfig('cbax-lexicon-content');
        }
    }
});
