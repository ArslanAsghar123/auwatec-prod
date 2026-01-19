const { Component } = Shopware;
const utils = Shopware.Utils;

import template from './acris-discount-group-create.html.twig';

Component.extend('acris-discount-group-create', 'acris-discount-group-detail', {
    template,

    beforeRouteEnter(to, from, next) {
        if (to.name.includes('acris.discount.group.create') && !to.params.id) {
            to.params.id = utils.createId();
            to.params.newItem = true;
        }

        next();
    },

    methods: {
        getEntity() {
            this.item = this.repository.create(Shopware.Context.api);
            this.item.priority = 10;
            this.item.discount = 0;
            this.item.minQuantity = 1;
            this.item.discountType = 'percentage';
            this.item.listPriceType = 'set';
            this.item.calculationBase = 'price';
            this.item.rrpTax = 'auto';
            this.item.rrpTaxDisplay = 'auto';
            this.item.calculationType = 'discount';
            this.item.customerAssignmentType = 'rules';
            this.item.productAssignmentType = 'dynamicProductGroup';
            this.item.active = true;
            this.item.excluded = true;
            this.item.accountDisplay = false;
        },

        saveFinish() {
            this.isSaveSuccessful = false;
            this.$router.push({ name: 'acris.discount.group.detail', params: { id: this.item.id } });
        },

        onClickSave() {
            this.isLoading = true;
            const titleSaveError = this.$tc('acris-discount-group.detail.titleSaveError');
            const messageSaveError = this.$tc('acris-discount-group.detail.messageSaveError');
            const titleSaveSuccess = this.$tc('acris-discount-group.detail.titleSaveSuccess');
            const messageSaveSuccess = this.$tc('acris-discount-group.detail.messageSaveSuccess');

            this.repository
                .save(this.item, Shopware.Context.api)
                .then(() => {
                    this.isLoading = false;
                    this.createNotificationSuccess({
                        title: titleSaveSuccess,
                        message: messageSaveSuccess
                    });
                    this.$router.push({ name: 'acris.discount.group.detail', params: { id: this.item.id } });
                }).catch(() => {
                    this.isLoading = false;
                    this.createNotificationError({
                        title: titleSaveError,
                        message: messageSaveError
                    });
                });
        }
    }
});
