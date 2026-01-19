import template from './footer-kit-module-list.html.twig';

const {Component, Mixin} = Shopware;
const {Criteria} = Shopware.Data;

Component.register('footer-kit-module-list', {
    template,
    inject: [
        'repositoryFactory'
    ],
    mixins: [
        Mixin.getByName('notification')
    ],
    data() {
        return {
            isLoading: false,
            footerKitItems: null,
        };
    },

    computed: {
        columns() {
            return [
                {
                    property: 'name',
                    dataIndex: 'name',
                    label: this.$tc('footer-kit.table.name'),
                    allowResize: true,
                    primary: true
                },{
                    property: 'salesChannel',
                    dataIndex: 'salesChannel',
                    label: this.$tc('footer-kit.table.salesChannel'),
                    allowResize: true
                }
            ];
        },
        footerKitRepository() {
            return this.repositoryFactory.create('cogi_footer_kit');
        }
    },

    created() {
        this.loadData();
    },

    methods: {
        loadData(){
            const criteria = new Criteria();
            criteria.addAssociation('salesChannel');

            this.footerKitRepository.search(criteria, Shopware.Context.api).then((result) => {
                this.footerKitItems = result;
            });
        },

        openCreate(){
            this.$router.push({name: 'footer.kit.module.create'});
        }
    }
});