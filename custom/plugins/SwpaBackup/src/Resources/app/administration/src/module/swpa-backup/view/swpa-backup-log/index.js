import template from './swpa-backup-log.html.twig';
import './swpabackup-log.scss';

const {Component, Mixin} = Shopware;
const {Criteria} = Shopware.Data;

Component.register('swpa-backup-log', {
    template,

    inject: ['acl', 'repositoryFactory'],

    mixins: [
        Mixin.getByName('notification'),
        Mixin.getByName('listing'),
        Mixin.getByName('placeholder')
    ],

    data() {
        return {
            logs: null,
            currencies: [],
            sortBy: 'createdAt',
            sortDirection: 'DESC',
            naturalSorting: true,
            isLoading: false,
            isBulkLoading: false,
            total: 0
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    computed: {
        repository() {
            return this.repositoryFactory.create('swpa_backup');
        },

        columns() {
            return this.getColumns();
        }
    },

    watch: {},

    methods: {
        getList() {
            this.isLoading = true;

            const productCriteria = new Criteria(this.page, this.limit);
            this.naturalSorting = this.sortBy === 'createdAt';
            productCriteria.addSorting(Criteria.sort(this.sortBy, this.sortDirection, this.naturalSorting));

            return Promise.all([
                this.repository.search(productCriteria, Shopware.Context.api)
            ]).then((result) => {
                const logs = result[0];

                this.total = logs.total;
                this.logs = logs;

                this.isLoading = false;
                this.selection = {};
            }).catch(() => {
                this.isLoading = false;
            });
        },

        getColumns() {
            return [{
                property: 'comment',
                label: this.$tc('swpa-backup.list.column.comment'),
                inlineEdit: 'string',
                allowResize: true,
                primary: true
            }, {
                property: 'filename',
                label: this.$tc('swpa-backup.list.column.filename'),
                inlineEdit: 'string',
                allowResize: true,
                primary: true
            }, {
                property: 'status',
                label: this.$tc('swpa-backup.list.column.status'),
                inlineEdit: 'boolean',
                allowResize: true,
                align: 'center'
            }, {
                property: 'time',
                label: this.$tc('swpa-backup.list.column.time'),
                inlineEdit: 'string',
                allowResize: true,
                align: 'center'
            },
                {
                    property: 'createdAt',
                    label: this.$tc('swpa-backup.list.column.createdAt'),
                    inlineEdit: 'string',
                    allowResize: true,
                    primary: true
                }];
        }
    }
});
