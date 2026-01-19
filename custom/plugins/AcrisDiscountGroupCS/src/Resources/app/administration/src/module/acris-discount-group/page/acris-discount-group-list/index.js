import template from './acris-discount-group-list.html.twig';
import './acris-discount-group-list.scss';

const {Component, Mixin} = Shopware;
const { Criteria } = Shopware.Data;

Component.register('acris-discount-group-list', {
    template,

    inject: ['repositoryFactory'],

    mixins: [
        Mixin.getByName('listing'),
        Mixin.getByName('notification'),
        Mixin.getByName('placeholder')
    ],

    data() {
        return {
            items: null,
            isLoading: false,
            showDeleteModal: false,
            repository: null,
            total: 0
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    computed: {
        entityRepository() {
            return this.repositoryFactory.create('acris_discount_group');
        },

        productRepository() {
            return this.repositoryFactory.create('product');
        },

        columns() {
            return this.getColumns();
        }
    },

    methods: {
        getList() {
            this.isLoading = true;
            const criteria = new Criteria(this.page, this.limit);
            criteria.setTerm(this.term);
            criteria.addAssociation('product');
            criteria.addAssociation('product.options.group');
            criteria.addAssociation('customer');
            criteria.addAssociation('rules');
            criteria.addAssociation('productStreams');
            criteria.addAssociation('productStreams');
            criteria.addSorting(Criteria.sort('priority', 'DESC'));

            this.entityRepository.search(criteria, Shopware.Context.api).then((items) => {
                this.total = items.total;
                this.items = items;
                this.items.forEach((item) => {
                    if (item.product && item.product.parentId) {
                        this.productRepository.get(item.product.parentId, Shopware.Context.api)
                            .then((parentProduct) => {
                                item.product.name = parentProduct.name;
                                item.product.translated.name = parentProduct.translated.name;
                            });
                    }
                });

                this.isLoading = false;

                return items;
            }).catch(() => {
                this.isLoading = false;
            });
        },

        onEditDiscountGroup(itemId) {
            this.$router.push({ name: 'acris.discount.group.detail', params: { id: itemId } });
        },

        getColumns() {
            return [{
                property: 'internalName',
                inlineEdit: 'string',
                label: 'acris-discount-group.list.columnInternalName',
                routerLink: 'acris.discount.group.detail',
                allowResize: true,
                primary: true
            }, {
                property: 'internalId',
                inlineEdit: 'string',
                label: 'acris-discount-group.list.columnInternalId',
                routerLink: 'acris.discount.group.detail',
                allowResize: true,
                primary: true
            }, {
                property: 'displayName',
                inlineEdit: 'string',
                label: 'acris-discount-group.list.columnDisplayName',
                routerLink: 'acris.discount.group.detail',
                allowResize: true
            }, {
                property: 'customerAssignmentType',
                label: 'acris-discount-group.list.columnCustomerAssignmentType',
                routerLink: 'acris.discount.group.detail',
                allowResize: true
            }, {
                property: 'productAssignmentType',
                label: 'acris-discount-group.list.columnProductAssignmentType',
                routerLink: 'acris.discount.group.detail',
                allowResize: true
            }, {
                property: 'active',
                label: 'acris-discount-group.list.columnActive',
                inlineEdit: 'boolean',
                width: '80px',
                allowResize: true,
                align: 'center'
            }, {
                property: 'discountType',
                label: 'acris-discount-group.list.columnDiscountType',
                routerLink: 'acris.discount.group.detail',
                allowResize: true
            }, {
                property: 'discount',
                label: 'acris-discount-group.list.columnDiscount',
                routerLink: 'acris.discount.group.detail',
                allowResize: true
            }, {
                property: 'minQuantity',
                label: 'acris-discount-group.list.columnMinQuantity',
                routerLink: 'acris.discount.group.detail',
                allowResize: true
            }, {
                property: 'maxQuantity',
                label: 'acris-discount-group.list.columnMaxQuantity',
                routerLink: 'acris.discount.group.detail',
                allowResize: true
            }, {
                property: 'priority',
                inlineEdit: 'number',
                label: 'acris-discount-group.list.columnPriority',
                routerLink: 'acris.discount.group.detail',
                allowResize: true
            }, {
                property: 'excluded',
                label: 'acris-discount-group.list.columnExcluded',
                inlineEdit: 'boolean',
                width: '80px',
                allowResize: true,
                align: 'center'
            }, {
                property: 'calculationBase',
                label: 'acris-discount-group.list.columnCalculationBase',
                routerLink: 'acris.discount.group.detail',
                allowResize: true
            }, {
                property: 'listPriceType',
                label: 'acris-discount-group.list.columnListPriceType',
                routerLink: 'acris.discount.group.detail',
                allowResize: true
            }, {
                property: 'accountDisplay',
                label: 'acris-discount-group.list.columnAccountDisplay',
                inlineEdit: 'boolean',
                width: '80px',
                allowResize: true,
                align: 'center'
                }
            ];
        }
    }
});

