import template from './acris-gpsr-contact-list.html.twig';
import './acris-gpsr-contact-list.scss';

const {Component, Mixin} = Shopware;
const {Criteria} = Shopware.Data;

Component.register('acris-gpsr-contact-list', {
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
            return this.repositoryFactory.create('acris_gpsr_contact');
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
                label: 'acris-gpsr-contact.list.columnInternalId',
                routerLink: 'acris.gpsr.contact.detail',
                allowResize: true,
                primary: true,
                width: '250px'
            },{
                property: 'internalName',
                inlineEdit: 'string',
                label: 'acris-gpsr-contact.list.columnInternalName',
                routerLink: 'acris.gpsr.contact.detail',
                allowResize: true,
                primary: true,
                width: '250px'
            }, {
                property: 'internalNotice',
                inlineEdit: 'string',
                label: 'acris-gpsr-contact.list.columnInternalNote',
                routerLink: 'acris.gpsr.contact.detail',
                allowResize: true,
                primary: true,
                width: '250px'
            }, {
                property: 'active',
                label: 'acris-gpsr-contact.list.columnActive',
                inlineEdit: 'boolean',
                allowResize: true,
                align: 'center',
                width: '80px'
            }, {
                property: 'priority',
                inlineEdit: 'number',
                label: 'acris-gpsr-contact.list.columnPriority',
                routerLink: 'acris.gpsr.contact.detail',
                allowResize: true,
                width: '80px'
            }, ];
        },

        onChangeLanguage(languageId) {
            this.getList(languageId);
        },
    }
});

