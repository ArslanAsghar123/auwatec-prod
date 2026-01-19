const { Component } = Shopware;
import template from './sw-product-detail.html.twig';

import errorDiscountGroupConfiguration from '../../../sw-product/page/sw-product-detail/error.cfg';
const { Criteria } = Shopware.Data;
const { mapPageErrors } = Shopware.Component.getComponentHelper();

Component.override('sw-product-detail', {
    template,

    computed: {
        ...mapPageErrors(errorDiscountGroupConfiguration),

        productCriteria() {
            const criteria = this.$super('productCriteria');
            criteria.addAssociation('acrisDiscountGroups');
            criteria.addAssociation('acrisDiscountGroups.product');
            criteria.addAssociation('acrisDiscountGroups.customer');
            criteria.addAssociation('acrisDiscountGroups.rules');
            criteria.addAssociation('acrisDiscountGroups.productStreams');
            criteria.getAssociation('acrisDiscountGroups').addSorting(Criteria.sort('priority', 'DESC'));

            return criteria;
        },

        isDisabled() {
            return Shopware.State.get('acrisDiscountGroup').isDisabled;
        }
    },

    methods: {
        onSave() {
            this.errorExists = false;

            if (this.product && this.product.extensions && this.product.extensions.acrisDiscountGroups) {
                this.product.acrisDiscountGroups = this.product.extensions.acrisDiscountGroups;
                const titleSaveError = this.$tc('acris-discount-group.product.titleSaveError');

                this.product.extensions.acrisDiscountGroups.forEach((discountGroup) => {
                    if (discountGroup.discount < 0 && !this.errorExists) {

                        const messageSaveError = this.$tc(
                            'acris-discount-group.product.messageSaveErrorDiscount', 0, {name: 'discount'}
                        );
                        this.errorExists = true;

                        this.createNotificationError({
                            title: titleSaveError,
                            message: messageSaveError
                        });
                    }

                    if (!discountGroup.discount && !this.errorExists) {
                        discountGroup.discount = 0;
                    }

                    if (discountGroup.customerAssignmentType === 'customer' && !this.errorExists) {
                        if (!discountGroup.customerId) {
                            const messageSaveError = this.$tc(
                                'acris-discount-group.product.messageSaveError', 0, {name: 'customerId'}
                            );
                            this.errorExists = true;

                            this.createNotificationError({
                                title: titleSaveError,
                                message: messageSaveError
                            });
                        }
                    } else {
                        if (discountGroup.customerAssignmentType === 'materialGroup' && !this.errorExists) {
                            if (!discountGroup.discountGroup) {
                                const messageSaveError = this.$tc(
                                    'acris-discount-group.product.messageSaveError', 0, {name: 'discountGroup'}
                                );
                                this.errorExists = true;

                                this.createNotificationError({
                                    title: titleSaveError,
                                    message: messageSaveError
                                });
                            }
                        } else {
                            if (discountGroup.customerAssignmentType === 'rules' && discountGroup.rules.length <= 0 && !this.errorExists) {
                                const messageSaveError = this.$tc(
                                    'acris-discount-group.product.messageSaveErrorRules', 0, {name: 'rules'}
                                );
                                this.errorExists = true;

                                this.createNotificationError({
                                    title: titleSaveError,
                                    message: messageSaveError
                                });
                            }
                        }
                    }
                });
            }

            if (!this.errorExists) {
                return this.$super('onSave');
            }
        }
    }
});
