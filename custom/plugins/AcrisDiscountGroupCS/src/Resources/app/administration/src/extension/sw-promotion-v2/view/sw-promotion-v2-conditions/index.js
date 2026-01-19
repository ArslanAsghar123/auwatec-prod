import template from './sw-promotion-v2-conditions.html.twig';

const { Component } = Shopware;

Component.override('sw-promotion-v2-conditions', {
    template,

    data() {
        return {
            preventCombinationDiscountGroup: false,
        };
    },

    methods: {
        createdComponent() {
            this.$super('createdComponent');
            this.preventCombinationDiscountGroup = this.promotion && this.promotion.customFields && this.promotion.customFields.acris_discount_group_promotion_prevent === true;
        },

        onPreventCombinationChange(value) {
            this.assignCustomField(value)
        },

        assignCustomField(value) {
            if (!this.promotion) return;
            if (this.promotion.customFields == null) {
                this.promotion.customFields = {
                    acris_discount_group_promotion_prevent: value
                }
            } else {
                this.promotion.customFields.acris_discount_group_promotion_prevent = value;
            }
        }
    }
});
