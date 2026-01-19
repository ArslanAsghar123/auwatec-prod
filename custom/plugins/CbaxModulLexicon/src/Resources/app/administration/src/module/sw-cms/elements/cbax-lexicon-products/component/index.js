import template from './sw-cms-el-cbax-lexicon-products.html.twig';
import './sw-cms-el-cbax-lexicon-products.scss';

const { Component, Mixin } = Shopware;

Component.register('sw-cms-el-cbax-lexicon-products', {
    template,

    mixins: [
        Mixin.getByName('cms-element')
    ],

    computed: {
        isCbaxLexiconCmsPage() {
            return this.cmsPageState.currentPage.type === 'cbax_lexicon';
        },

        demoProductElement() {
            return {
                config: {
                    boxLayout: {
                        source: 'static',
                        value: 'standard'
                    },
                    displayMode: {
                        source: 'static',
                        value: 'standard'
                    }
                },
                data: {
                    product: {
                        name: 'Lorem Ipsum dolor',
                        description: `Lorem ipsum dolor sit amet, consetetur sadipscing elitr,
                        sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat,
                        sed diam voluptua.`.trim(),
                        price: [
                            { gross: 19.90 }
                        ],
                        cover: {
                            media: {
                                url: '/administration/static/img/cms/preview_glasses_large.jpg',
                                alt: 'Lorem Ipsum dolor'
                            }
                        }
                    }
                }

            };
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('cbax-lexicon-products');
        }
    }
});
