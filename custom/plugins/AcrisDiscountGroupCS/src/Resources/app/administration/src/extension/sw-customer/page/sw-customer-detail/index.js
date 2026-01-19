import template from './sw-customer-detail.html.twig';

const { Component } = Shopware;
const { Criteria } = Shopware.Data;

Component.override('sw-customer-detail', {
    template,

    computed: {
        defaultCriteria() {
            const criteria = this.$super('defaultCriteria');
            criteria.addAssociation('acrisDiscountGroups');
            criteria.addAssociation('acrisDiscountGroups.product');
            criteria.addAssociation('acrisDiscountGroups.customer');
            criteria.addAssociation('acrisDiscountGroups.rules');
            criteria.addAssociation('acrisDiscountGroups.productStreams');
            criteria.getAssociation('acrisDiscountGroups').addSorting(Criteria.sort('priority', 'DESC'));

            return criteria;
        }
    },

    methods: {
        async onSave() {
            this.errorExists = false;

            if (this.customer && this.customer.extensions && this.customer.extensions.acrisDiscountGroups) {
                this.customer.acrisDiscountGroups = this.customer.extensions.acrisDiscountGroups;
                const titleSaveError = this.$tc('acris-discount-group.customer.titleSaveError');

                this.customer.extensions.acrisDiscountGroups.forEach((discountGroup) => {
                    if (discountGroup.discount < 0 && !this.errorExists) {
                        const messageSaveError = this.$tc(
                            'acris-discount-group.customer.messageSaveErrorDiscount', 0, {name: 'discount'}
                        );
                        this.errorExists = true;

                        this.createNotificationError({
                            title: titleSaveError,
                            message: messageSaveError
                        });
                    }

                    if (!discountGroup.productAssignmentType && !this.errorExists) {
                        const messageSaveError = this.$tc('acris-discount-group.customer.messageSaveErrorProductAssignmentTypeError');
                        this.errorExists = true;

                        this.createNotificationError({
                            title: titleSaveError,
                            message: messageSaveError
                        });
                    }

                    if (!discountGroup.discount && !this.errorExists) {
                        discountGroup.discount = 0;
                    }

                    if (discountGroup.productAssignmentType === 'product' && !this.errorExists) {
                        if (!discountGroup.productId) {
                            const messageSaveError = this.$tc(
                                'acris-discount-group.customer.messageSaveError', 0, {name: 'productId'}
                            );
                            this.errorExists = true;

                            this.createNotificationError({
                                title: titleSaveError,
                                message: messageSaveError
                            });
                        }
                    } else {
                        if (discountGroup.productAssignmentType === 'materialGroup' && !this.errorExists) {
                            if (!discountGroup.materialGroup) {
                                const messageSaveError = this.$tc(
                                    'acris-discount-group.customer.messageSaveError', 0, {name: 'discountGroup'}
                                );
                                this.errorExists = true;

                                this.createNotificationError({
                                    title: titleSaveError,
                                    message: messageSaveError
                                });
                            }
                        } else {
                            if (discountGroup.productAssignmentType === 'dynamicProductGroup' && discountGroup.productStreams.length <= 0 && !this.errorExists) {
                                const messageSaveError = this.$tc(
                                    'acris-discount-group.customer.messageSaveErrorDynamicProductGroups', 0, {name: 'productDynamicGroups'}
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
    },
});
