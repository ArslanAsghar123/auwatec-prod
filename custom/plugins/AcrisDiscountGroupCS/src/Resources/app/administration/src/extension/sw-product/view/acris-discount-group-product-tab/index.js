const { Component } = Shopware;
const { mapGetters, mapState} = Shopware.Component.getComponentHelper();
import template from './acris-discount-group-product-tab.html.twig';
import './acris-discount-group-product-tab.scss';

Component.register('acris-discount-group-product-tab', {
    template,

    inject: ['repositoryFactory', 'acl'],

    props: {
        allowEdit: {
            type: Boolean,
            required: false,
            default: true
        }
    },

    data() {
        return {
            discountGroup: null,
        };
    },

    computed: {
        ...mapState('swProductDetail', [
            'product'
        ]),

        ...mapGetters('swProductDetail', [
            'isLoading'
        ]),

        discountGroupValue() {
            if (this.product.customFields == null) {
                this.product.customFields = {
                    acris_discount_group_product_value: null
                }
            }

            if (!this.product.customFields.acris_discount_group_product_value) {
                this.product.customFields.acris_discount_group_product_value = null;
                return null;
            }

            return this.product.customFields.acris_discount_group_product_value;
        },

        assetFilter() {
            return Shopware.Filter.getByName('asset');
        },
    },

    methods: {
        createdComponent() {
            this.updateProductDiscountGroupCustomFields(true);
        },

        updatedComponent() {
            this.updateProductDiscountGroupCustomFields();
        },

        onAddDiscountGroup() {
            const discountGroupRepository = this.repositoryFactory.create(
                this.product.extensions.acrisDiscountGroups.entity,
                this.product.extensions.acrisDiscountGroups.source
            );
            this.discountGroup = discountGroupRepository.create(Shopware.Context.api);
            this.discountGroup.productId = this.product.id;
            this.discountGroup.productAssignmentType = 'product';
            this.discountGroup.priority = 10;
            this.discountGroup.discount = 0;
            this.discountGroup.minQuantity = 1;
            this.discountGroup.discountType = 'percentage';
            this.discountGroup.listPriceType = 'ignore';
            this.discountGroup.calculationType = 'discount';
            this.discountGroup.customerAssignmentType = 'rules';
            this.discountGroup.active = true;
            this.discountGroup.excluded = true;

            this.product.extensions.acrisDiscountGroups.push(this.discountGroup);
        },

        discountGroupValueChange(value) {
            this.product.customFields.acris_discount_group_product_value = value;
        }
    }
});
