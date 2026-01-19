import template from './sw-cms-el-cbax-lexicon-sidebar.html.twig';
import './sw-cms-el-cbax-lexicon-sidebar.scss';

const { Component, Mixin } = Shopware;

Component.register('sw-cms-el-cbax-lexicon-sidebar', {
    template,

    mixins: [
        Mixin.getByName('cms-element')
    ],

    computed: {
        isCbaxLexiconCmsPage() {
            return this.cmsPageState.currentPage.type === 'cbax_lexicon';
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('cbax-lexicon-sidebar');
        }
    }
});
