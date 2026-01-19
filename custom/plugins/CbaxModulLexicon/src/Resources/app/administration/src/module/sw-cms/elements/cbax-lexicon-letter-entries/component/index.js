import template from './sw-cms-el-cbax-lexicon-letter-entries.html.twig';
import './sw-cms-el-cbax-lexicon-letter-entries.scss';

const { Component, Mixin } = Shopware;

Component.register('sw-cms-el-cbax-lexicon-letter-entries', {
    template,

    mixins: [
        Mixin.getByName('cms-element')
    ],

    data() {
        return {
            demoBoxCount: 6,
            boxStyle: "",
            showSlider: false
        };
    },

    computed: {
        isCbaxLexiconCmsPage() {
            return this.cmsPageState.currentPage.type === 'cbax_lexicon';
        }
    },

    watch: {
        'element.config.template.value': {
            handler() {
                this.setBoxStyle();
            }
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('cbax-lexicon-letter-entries');
            this.setBoxStyle();
        },

        setBoxStyle() {
            switch (this.element.config.template.value) {
                case 'listing_1col':
                    this.boxStyle = "grid-template-columns: 1fr;";
                    this.showSlider = false;
                    break;
                case 'listing_2col':
                    this.boxStyle = "grid-template-columns: 1fr 1fr;";
                    this.showSlider = false;
                    break;
                case 'listing_3col':
                    this.boxStyle = "grid-template-columns: 1fr 1fr 1fr;";
                    this.showSlider = false;
                    break;
                case 'listing_4col':
                    this.boxStyle = "grid-template-columns: 1fr 1fr 1fr 1fr;";
                    this.showSlider = false;
                    break;
                default:
                    this.boxStyle = "grid-template-columns: 1fr 1fr 1fr;";
                    this.showSlider = false;
                    break;
            }
        }
    }
});
