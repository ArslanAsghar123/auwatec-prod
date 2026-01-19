import template from './sw-cms-product-preview-aku-cms-factory.html.twig';

const { Component, Mixin } = Shopware;
const { Criteria, EntityCollection } = Shopware.Data;

Shopware.Component.register('sw-cms-el-product-preview-aku-cms-factory', {
    template,
    props: {
        product_id: {
            type: String
        },

    },
    inject: ['repositoryFactory'],
    data(){
        return {
            product: null,
        }
    },
    computed: {
        productRepository() {
            return this.repositoryFactory.create('product');
        },
        productCriteria() {
            const criteria = new Criteria();
            criteria.setIds([this.product_id]);
            criteria.addAssociation('options.group');
            return criteria;
        },
        productSelectContext() {
            const context = Object.assign({}, Shopware.Context.api);
            context.inheritance = true;

            return context;
        },
    },
    methods: {
        async loadData(){
            let that = this;
            let uuidRegex = new RegExp('^[0-9a-fA-F]{32}$');
            this.product = null;
            if (null == this.product_id || !uuidRegex.test(String(this.product_id))) {
                return;
            }
            this.productRepository
                .search(this.productCriteria, this.productSelectContext)
                .then((result) => {
                    this.product = result.first()
                });
        }, 

    },
    created() {
        this.loadData();
    },
    watch: {
        product_id(){
            this.loadData();
        }
    }
});