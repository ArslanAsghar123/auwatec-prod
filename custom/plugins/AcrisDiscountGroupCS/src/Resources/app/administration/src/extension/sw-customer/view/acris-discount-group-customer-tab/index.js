const { Component } = Shopware;
import template from './acris-discount-group-customer-tab.html.twig';
import './acris-discount-group-customer-tab.scss';

Component.register('acris-discount-group-customer-tab', {
    template,

    inject: ['repositoryFactory', 'acl'],

    props: {
        customer: {
            type: Object,
            required: true,
        },

        customerEditMode: {
            type: Boolean,
            required: true,
            default: false,
        },

        isLoading: {
            type: Boolean,
            required: false,
            default: false,
        },
    },

    data() {
        return {
            discountGroup: null
        };
    },

    computed: {
        discountGroupValue() {
            if (this.customer.customFields == null) {
                this.customer.customFields = {
                    acris_discount_group_customer_value: null
                }
            }

            if (!this.customer.customFields.acris_discount_group_customer_value) return null;

            return this.customer.customFields.acris_discount_group_customer_value;
        },

        assetFilter() {
            return Shopware.Filter.getByName('asset');
        },
    },

    methods: {
        onAddDiscountGroup() {
            const discountGroupRepository = this.repositoryFactory.create(
                this.customer.extensions.acrisDiscountGroups.entity,
                this.customer.extensions.acrisDiscountGroups.source
            );
            this.discountGroup = discountGroupRepository.create(Shopware.Context.api);
            this.discountGroup.customerId = this.customer.id;
            this.discountGroup.customerAssignmentType = 'customer';
            this.discountGroup.priority = 10;
            this.discountGroup.discount = 0;
            this.discountGroup.minQuantity = 1;
            this.discountGroup.discountType = 'percentage';
            this.discountGroup.listPriceType = 'ignore';
            this.discountGroup.calculationType = 'discount';
            this.discountGroup.productAssignmentType = 'dynamicProductGroup';
            this.discountGroup.active = true;
            this.discountGroup.excluded = true;

            this.customer.extensions.acrisDiscountGroups.push(this.discountGroup);
        },

        discountGroupValueChange(value) {
            this.customer.customFields.acris_discount_group_customer_value = value;
        }
    }
});
