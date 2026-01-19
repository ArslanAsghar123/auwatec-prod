import template from './sw-cms-el-cbax-lexicon-latest-entries.html.twig';
import './sw-cms-el-cbax-lexicon-latest-entries.scss';

const { Component, Mixin } = Shopware;

Component.register('sw-cms-el-cbax-lexicon-latest-entries', {
    template,

    mixins: [
        Mixin.getByName('cms-element')
    ],

    data() {
        return {
            demoBoxCount: 3,
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
        },
        'element.config.entryNumber.value': {
            handler() {
                this.setDemoBoxCount();
            }
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('cbax-lexicon-latest-entries');
            this.setDemoBoxCount();
            this.setBoxStyle();
        },

        setDemoBoxCount() {
            if (this.element.config.entryNumber.value !== undefined && this.element.config.entryNumber.value > 0) {
                this.demoBoxCount = this.element.config.entryNumber.value;
            } else {
                this.demoBoxCount = 3;
            }
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
                case 'slider':
                    this.boxStyle = "";
                    this.showSlider = true;
                    break;
                default:
                    this.boxStyle = "grid-template-columns: 1fr 1fr 1fr;";
                    this.showSlider = false;
                    break;
            }
        }
    }
});
