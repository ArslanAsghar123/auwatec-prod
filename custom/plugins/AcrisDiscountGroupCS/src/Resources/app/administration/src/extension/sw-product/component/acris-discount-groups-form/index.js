import template from './acris-discount-groups-form.html.twig';

const { Criteria } = Shopware.Data;
const { Component } = Shopware;
const { mapPropertyErrors, mapGetters, mapState } = Component.getComponentHelper();
import DiscountHandler from './../../../../module/acris-discount-group/page/acris-discount-group-detail/handler';

const discountHandler = new DiscountHandler();

Component.register('acris-discount-groups-form', {
    template,

    inject: ['repositoryFactory', 'acl'],

    props: {
        acrisDiscountGroup: {
            type: Object,
            required: true
        },
        allowEdit: {
            type: Boolean,
            required: false,
            default: true
        }
    },

    data() {
        return {
            showDeleteModal: false,
            showModalPreview: false,
            productStream: null,
            productStreamFilter: [],
            optionSearchTerm: '',
            useManualAssignment: false,
            showLayoutModal: false,
            emptyCmsPage: null,
            sortBy: 'name',
            sortDirection: 'ASC',
            assignmentKey: 0,
            currencies: [],
            defaultCurrency: null,
            currencySymbol: null
        };
    },

    computed: {
        ...mapPropertyErrors('acrisDiscountGroup', [
            'discount',
            'customerAssignmentType',
            'productAssignmentType'
        ]),

        ...mapState('swProductDetail', [
            'product'
        ]),

        ...mapGetters('swProductDetail', [
            'isLoading'
        ]),

        discountSuffix() {
            return discountHandler.getValueSuffix(this.acrisDiscountGroup.discountType, this.currencySymbol);
        },

        currencyRepository() {
            return this.repositoryFactory.create('currency');
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
                label: this.$tc('acris-discount-group.detail.listPriceOptionRemove'),
                value: 'remove'
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

        sortingConCat() {
            return `${this.acrisDiscountGroup.sortBy}:${this.acrisDiscountGroup.sortDirection}`;
        },

        disablePositioning() {
            return (!!this.term) || (this.sortBy !== 'position');
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.currencyRepository.search(new Criteria(), Shopware.Context.api).then((response) => {
                this.currencies = response;
                this.defaultCurrency = this.currencies.find(currency => currency.isSystemDefault);
                this.currencySymbol = this.defaultCurrency.symbol;
            });
        },

        onShowDeleteModal() {
            this.showDeleteModal = true;
        },

        onCloseDeleteModal() {
            this.showDeleteModal = false;
        },

        onConfirmDelete() {
            this.onCloseDeleteModal();
            this.$nextTick(() => {
                this.product.extensions.acrisDiscountGroups.remove(this.acrisDiscountGroup.id);
            });
        },

        onSortingChanged(value) {
            [this.acrisDiscountGroup.sortBy, this.acrisDiscountGroup.sortDirection] = value.split(':');
        },
    }
});
