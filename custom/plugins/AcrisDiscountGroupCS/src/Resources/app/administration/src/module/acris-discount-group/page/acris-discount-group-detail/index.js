const { Component } = Shopware;
const { Mixin } = Shopware;
const { Criteria } = Shopware.Data;
import DiscountHandler from './handler';

const discountHandler = new DiscountHandler();

import template from './acris-discount-group-detail.html.twig';
import './acris-discount-group-detail.scss';

Component.register('acris-discount-group-detail', {
    template,

    inject: ['repositoryFactory', 'context'],

    mixins: [
        Mixin.getByName('notification'),
        Mixin.getByName('placeholder')
    ],

    data() {
        return {
            item: null,
            isLoading: false,
            processSuccess: false,
            repository: null,
            isSaveSuccessful: false,
            currencies: [],
            defaultCurrency: null,
            currencySymbol: null
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    created() {
        this.createdComponent();
    },

    computed: {
        discountSuffix() {
            return discountHandler.getValueSuffix(this.item.discountType, this.currencySymbol);
        },

        currencyRepository() {
            return this.repositoryFactory.create('currency');
        },

        discountGroupCriteria() {
            const criteria = new Criteria();
            criteria.addAssociation('rules');
            criteria.addAssociation('productStreams');

            return criteria
        },

        isDisabled() {
            if (this.isLoading || !this.item.customerAssignmentType || !this.item.productAssignmentType || this.item.discount < 0 || !this.item.minQuantity) return true;

            if (this.item.customerAssignmentType === 'customer') {
                if (!this.item.customerId) return true;
            } else {
                if (this.item.customerAssignmentType === 'materialGroup') {
                    if (!this.item.discountGroup) return true;
                } else if (this.item.customerAssignmentType !== 'everyCustomer') {
                    if (!this.item.rules || this.item.rules.length <= 0) return true;
                }
            }

            if (this.item.productAssignmentType === 'product') {
                if (!this.item.productId) return true;
            } else {
                if (this.item.productAssignmentType === 'materialGroup') {
                    if (!this.item.materialGroup) return true;
                } else if(this.item.productAssignmentType !== 'everyProduct') {
                    if (!this.item.productStreams || this.item.productStreams.length <= 0) return true;
                }
            }

            if (this.item.accountDisplay && !this.item.displayText) {
                return true;
            }

            return false;
        },

        productSelectContext() {
            return {
                ...Shopware.Context.api,
                inheritance: true,
            };
        },

        productCriteria() {
            const criteria = new Criteria();
            criteria.addAssociation('options.group');

            return criteria;
        },

        discountTypes() {
            return [{
                label: this.$tc('acris-discount-group.detail.absoluteOption'),
                value: 'absolute'
            }, {
                label: this.$tc('acris-discount-group.detail.percentageOption'),
                value: 'percentage'
            }];
        },

        customerAssignmentTypes() {
            return [{
                label: this.$tc('acris-discount-group.detail.customerAssignmentOptionCustomer'),
                value: 'customer'
            }, {
                label: this.$tc('acris-discount-group.detail.customerAssignmentOptionMaterialGroup'),
                value: 'materialGroup'
            }, {
                label: this.$tc('acris-discount-group.detail.customerAssignmentOptionRules'),
                value: 'rules'
            }, {
                label: this.$tc('acris-discount-group.detail.customerAssignmentOptionEveryCustomer'),
                value: 'everyCustomer'
            }];
        },

        productAssignmentTypes() {
            return [{
                label: this.$tc('acris-discount-group.detail.productAssignmentOptionProduct'),
                value: 'product'
            }, {
                label: this.$tc('acris-discount-group.detail.productAssignmentOptionMaterialGroup'),
                value: 'materialGroup'
            }, {
                label: this.$tc('acris-discount-group.detail.productAssignmentOptionDynamicProductGroup'),
                value: 'dynamicProductGroup'
            }, {
                label: this.$tc('acris-discount-group.detail.productAssignmentOptionEveryProduct'),
                value: 'everyProduct'
            }];
        },

        listPriceTypes() {
            return [{
                label: this.$tc('acris-discount-group.detail.listPriceOptionIgnore'),
                value: 'ignore'
            }, {
                label: this.$tc('acris-discount-group.detail.listPriceOptionSet'),
                value: 'set'
            }, {
                label: this.$tc('acris-discount-group.detail.listPriceOptionSetPrice'),
                value: 'setPrice'
            }, {
                label: this.$tc('acris-discount-group.detail.listPriceOptionRrp'),
                value: 'rrp'
            }, {
                label: this.$tc('acris-discount-group.detail.listPriceOptionSetRrp'),
                value: 'setRrp'
            }, {
                label: this.$tc('acris-discount-group.detail.listPriceOptionPurchasePrice'),
                value: 'purchasePrice'
            }, {
                label: this.$tc('acris-discount-group.detail.listPriceOptionRemove'),
                value: 'remove'
            }];
        },

        calculationBaseOptions() {
            return [{
                label: this.$tc('acris-discount-group.detail.calculationBaseOptionPrice'),
                value: 'price'
            }, {
                label: this.$tc('acris-discount-group.detail.calculationBaseOptionListPrice'),
                value: 'listPrice'
            }, {
                label: this.$tc('acris-discount-group.detail.calculationBaseOptionRrp'),
                value: 'rrp'
            }, {
                label: this.$tc('acris-discount-group.detail.calculationBaseOptionPurchasePrice'),
                value: 'purchasePrice'
            }];
        },

        rrpTaxOptions() {
            return [{
                label: this.$tc('acris-discount-group.detail.rrpTaxOptionAuto'),
                value: 'auto'
            }, {
                label: this.$tc('acris-discount-group.detail.rrpTaxOptionGross'),
                value: 'gross'
            }, {
                label: this.$tc('acris-discount-group.detail.rrpTaxOptionNet'),
                value: 'net'
            }];
        },

        rrpTaxDisplayOptions() {
            return [{
                label: this.$tc('acris-discount-group.detail.rrpTaxOptionAuto'),
                value: 'auto'
            }, {
                label: this.$tc('acris-discount-group.detail.rrpTaxOptionGross'),
                value: 'gross'
            }, {
                label: this.$tc('acris-discount-group.detail.rrpTaxOptionNet'),
                value: 'net'
            }];
        },

        calculationTypes() {
            return [{
                label: this.$tc('acris-discount-group.detail.calculationOptionSurcharge'),
                value: 'surcharge'
            }, {
                label: this.$tc('acris-discount-group.detail.calculationOptionDiscount'),
                value: 'discount'
            }];
        },

        languageStore() {
            return StateDeprecated.getStore('language');
        }
    },

    methods: {
        createdComponent(){
            this.repository = this.repositoryFactory.create('acris_discount_group');
            this.getEntity();

            this.currencyRepository.search(new Criteria(), Shopware.Context.api).then((response) => {
                this.currencies = response;
                this.defaultCurrency = this.currencies.find(currency => currency.isSystemDefault);
                this.currencySymbol = this.defaultCurrency.symbol;
            });
        },

        getEntity() {
            this.repository
                .get(this.$route.params.id, Shopware.Context.api, this.discountGroupCriteria)
                .then((entity) => {
                    this.item = entity;
                });
        },

        onClickSave() {
            this.isLoading = true;
            const titleSaveError = this.$tc('acris-discount-group.detail.titleSaveError');
            const messageSaveError = this.$tc('acris-discount-group.detail.messageSaveError');
            const titleSaveSuccess = this.$tc('acris-discount-group.detail.titleSaveSuccess');
            const messageSaveSuccess = this.$tc('acris-discount-group.detail.messageSaveSuccess');

            this.isSaveSuccessful = false;
            this.isLoading = true;

            this.repository
                .save(this.item, Shopware.Context.api)
                .then(() => {
                    this.getEntity();
                    this.isLoading = false;
                    this.processSuccess = true;
                    this.createNotificationSuccess({
                        title: titleSaveSuccess,
                        message: messageSaveSuccess
                    });
                }).catch(() => {
                    this.isLoading = false;
                    this.createNotificationError({
                    title: titleSaveError,
                    message: messageSaveError
                });
            });
        },

        saveFinish() {
            this.processSuccess = false;
        },

        onChangeLanguage() {
            this.getEntity();
        }
    }
});
