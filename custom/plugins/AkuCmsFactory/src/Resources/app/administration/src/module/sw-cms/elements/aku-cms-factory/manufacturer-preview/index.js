import template from './sw-cms-manufacturer-preview-aku-cms-factory.html.twig';

const { Component, Mixin } = Shopware;
const { Criteria, EntityCollection } = Shopware.Data;

Shopware.Component.register('sw-cms-el-manufacturer-preview-aku-cms-factory', {
    template,
    props: {
        manufacturer_id: {
            type: Array
        },

    },
    inject: ['repositoryFactory'],
    data(){
        return {
            manufacturers: null,
        }
    },
    computed: {
        manufacturerepository() {
            return this.repositoryFactory.create('product_manufacturer');
        },
        manufacturerCriteria() {
            const criteria = new Criteria();
            criteria.setIds(this.manufacturer_id);
            return criteria;
        },
        manufacturerSelectContext() {
            const context = Object.assign({}, Shopware.Context.api);
            context.inheritance = true;
            return context;
        },
    },
    methods: {
        async loadData(){
            this.manufacturers = null;
            if (null == this.manufacturer_id  || !Array.isArray(this.manufacturer_id) ||this.manufacturer_id.length <= 0) {
                return;
            }
            this.manufacturerepository
                .search(this.manufacturerCriteria, this.manufacturerSelectContext)
                .then((result) => {
                    this.manufacturers = result;
                });
        }, 

    },
    created() {
        this.loadData();
    },
    watch: {
        manufacturer_id(){
            this.loadData();
        }
    }
});