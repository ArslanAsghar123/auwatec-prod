import template from './sw-cms-el-last-seen-product-slider.html.twig';
import './sw-cms-el-last-seen-product-slider.scss';

const { Component, Mixin } = Shopware;

Component.register('sw-cms-el-last-seen-product-slider', {
    template,

    mixins: [
        Mixin.getByName('cms-element')
    ],

    data() {
        return {
            sliderBoxLimit: 4,
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
                    product: {
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

        hasNavigation() {
            return !!this.element.config.navigation.value;
        },

        classes() {
            return {
                'has--navigation': this.hasNavigation,
                'has--pagination': this.hasPagination
            };
        },

        currentDeviceView() {
            return this.cmsPageState.currentCmsDeviceView;
        }
    },

    watch: {
        'element.config.desktopItems.value': {
            handler() {
                this.setSliderRowLimit();
            }
        },

        'element.config.mobileItems.value': {
            handler() {
                this.setSliderRowLimit();
            }
        },

        currentDeviceView() {
            setTimeout(() => {
                this.setSliderRowLimit();
            }, 400);
        }
    },

    created() {
        this.createdComponent();
    },

    mounted() {
        this.mountedComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('last-seen-product-slider');
        },

        mountedComponent() {
            this.setSliderRowLimit();
        },

        setSliderRowLimit() {
            if (this.currentDeviceView === 'mobile'
                || (this.$refs.productHolder && this.$refs.productHolder.offsetWidth < 500)
            ) {
                this.sliderBoxLimit = 1;
                return;
            }

            if (!this.element.config.elMinWidth.value ||
                this.element.config.elMinWidth.value === 'px' ||
                this.element.config.elMinWidth.value.indexOf('px') === -1) {
                this.sliderBoxLimit = 3;
                return;
            }

            if (parseInt(this.element.config.elMinWidth.value.replace('px', ''), 10) <= 0) {
                return;
            }

            // Subtract to fake look in storefront which has more width
            const fakeLookWidth = 100;
            const boxWidth = this.$refs.productHolder.offsetWidth;
            const elGap = 32;
            let elWidth = parseInt(this.element.config.elMinWidth.value.replace('px', ''), 10);

            if (elWidth >= 300) {
                elWidth -= fakeLookWidth;
            }

            this.sliderBoxLimit = Math.floor(boxWidth / (elWidth + elGap));
        },

    }
});
