const { Component } = Shopware;
const Criteria = Shopware.Data.Criteria;
import template from './sw-product-detail.html.twig';
const { mapGetters, mapState} = Shopware.Component.getComponentHelper();

Component.override('sw-product-detail', {
    template,
    inject: ['systemConfigApiService'],

    computed: {
        customFieldSetCriteria() {
            const criteria = this.$super('customFieldSetCriteria');
            criteria.addFilter(Criteria.not('and', [Criteria.equalsAny('name', this.excludedCustomFieldSets)]));

            return criteria;
        },

        productCriteria() {
            const criteria = this.$super('productCriteria');
            criteria.addAssociation('acrisGpsrDownloads');

            return criteria;
        },
        ...mapGetters('swProductDetail', [
            'isChild',
        ]),
        identifier() {
            return this.productTitle;
        },

        productTitle() {
            // when product is variant
            if (this.isChild && this.product) {
                return this.getInheritTitle();
            }

            if (!this.$i18n) {
                return '';
            }

            // return name
            return this.placeholder(this.product, 'name', this.$tc('sw-product.detail.textHeadline'));
        },
    },
    data() {
        return {
            displaySetting: 'folded',
            excludedCustomFieldSets: [
                'acris_gpsr_product'
            ]
        };
    },
    metaInfo() {
        return {
            title: this.$createTitle(this.identifier),
        };
    },
    created() {
        this.getDisplay();

    },
    methods: {
        getInheritTitle() {
            if (
                this.product.hasOwnProperty('translated') &&
                this.product.translated.hasOwnProperty('name') &&
                this.product.translated.name !== null
            ) {
                return this.product.translated.name;
            }
            if (this.product.name !== null) {
                return this.product.name;
            }
            if (this.parentProduct && this.parentProduct.hasOwnProperty('translated')) {
                const pProduct = this.parentProduct;
                return pProduct.translated.hasOwnProperty('name') ? pProduct.translated.name : pProduct.name;
            }
            return '';
        },
        async getDisplay() {
            let config = await this.systemConfigApiService.getValues('AcrisGpsrCS.config')
            this.displaySetting = config['AcrisGpsrCS.config.gpsrDisplay'];

        },
        loadProduct() {
            Shopware.State.commit('swProductDetail/setLoading', ['product', true]);

            return this.productRepository.get(
                this.productId || this.product.id,
                Shopware.Context.api,
                this.productCriteria,
            ).then((product) => {
                if (!product.purchasePrices?.length > 0 && !product.parentId) {
                    product.purchasePrices = this.getDefaultPurchasePrices();
                }
                if(!product.customFields) {
                    product.customFields = {};
                }

                if(!product.customFields.acris_gpsr_product_manufacturer) {
                    product.customFields.acris_gpsr_product_manufacturer = '';
                }

                if(!product.customFields.acris_gpsr_product_contact) {
                    product.customFields.acris_gpsr_product_contact = '';
                }

                if(!product.customFields.acris_gpsr_product_hint_warning) {
                    product.customFields.acris_gpsr_product_hint_warning = '';
                }

                if(!product.customFields.acris_gpsr_product_hint_safety) {
                    product.customFields.acris_gpsr_product_hint_safety = '';
                }

                if(!product.customFields.acris_gpsr_product_hint_information) {
                    product.customFields.acris_gpsr_product_hint_information = '';
                }
                Shopware.State.commit('swProductDetail/setProduct', product);

                if (this.product.parentId) {
                    this.loadParentProduct();
                } else {
                    Shopware.State.commit('swProductDetail/setParentProduct', {});
                }

                Shopware.State.commit('swProductDetail/setLoading', ['product', false]);
            });
        },

        loadParentProduct() {
            Shopware.State.commit('swProductDetail/setLoading', ['parentProduct', true]);

            return this.productRepository.get(this.product.parentId, Shopware.Context.api, this.productCriteria)
                .then((res) => {
                    if(!res.customFields) {
                        res.customFields = {};
                    }

                    Shopware.State.commit('swProductDetail/setParentProduct', res);
                }).then(() => {
                    Shopware.State.commit('swProductDetail/setLoading', ['parentProduct', false]);
                });
        },
    }


});
