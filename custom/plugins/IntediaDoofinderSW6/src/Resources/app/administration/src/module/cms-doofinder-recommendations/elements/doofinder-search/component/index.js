const { Component, Mixin } = Shopware;
import template from './index.html.twig';
import './index.scss';

Component.register('doofinder-element-component', {
    template,

    mixins: [
        Mixin.getByName('cms-element')
    ],

    data() {
        return {
            sliderBoxLimit: 3
        };
    },

    computed: {
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
                    manufacturer: {
                        name: 'Lorem ipsum dolor',
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
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('intedia-doofinder-recommendation-element');
        }
    }
});
