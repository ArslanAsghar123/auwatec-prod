import template from './acris-gpsr-manufacturer-list.html.twig';
import './acris-gpsr-manufacturer-list.scss';

const {Component, Mixin} = Shopware;
const {Criteria} = Shopware.Data;

Component.register('acris-gpsr-manufacturer-list', {
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
            return this.repositoryFactory.create('acris_gpsr_mf');
        },

        columns() {
            return this.getColumns();
        }
    },

    methods: {
        getList() {
            this.isLoading = true;
            const criteria = new Criteria(this.page, this.limit);
            criteria.addSorting(Criteria.sort('priority', 'DESC'));
            criteria.addSorting(Criteria.sort('internalName', 'ASC'));
            criteria.setTerm(this.term);

            this.entityRepository.search(criteria, Shopware.Context.api).then((items) => {
                this.total = items.total;
                this.items = items;
                this.isLoading = false;

                return items;
            }).catch(() => {
                this.isLoading = false;
            });
        },

        onDelete(id) {
            this.showDeleteModal = id;
        },

        onCloseDeleteModal() {
            this.showDeleteModal = false;
        },

        getColumns() {
            return [{
                property: 'internalId',
                inlineEdit: 'string',
                label: 'acris-gpsr-manufacturer.list.columnInternalId',
                routerLink: 'acris.gpsr.manufacturer.detail',
                allowResize: true,
                primary: true,
                width: '250px'
            },{
                property: 'internalName',
                inlineEdit: 'string',
                label: 'acris-gpsr-manufacturer.list.columnInternalName',
                routerLink: 'acris.gpsr.manufacturer.detail',
                allowResize: true,
                primary: true,
                width: '250px'
            }, {
                property: 'internalNotice',
                inlineEdit: 'string',
                label: 'acris-gpsr-manufacturer.list.columnInternalNote',
                routerLink: 'acris.gpsr.manufacturer.detail',
                allowResize: true,
                primary: true,
                width: '250px'
            }, {
                property: 'active',
                label: 'acris-gpsr-manufacturer.list.columnActive',
                inlineEdit: 'boolean',
                allowResize: true,
                align: 'center',
                width: '80px'
            }, {
                property: 'priority',
                inlineEdit: 'number',
                label: 'acris-gpsr-manufacturer.list.columnPriority',
                routerLink: 'acris.gpsr.manufacturer.detail',
                allowResize: true,
                width: '80px'
            }, ];
        },

        onChangeLanguage(languageId) {
            this.getList(languageId);
        },
    }
});

