import template from './sw-cms-product-stream-preview-aku-cms-factory.html.twig';

const { Component, Mixin } = Shopware;
const { Criteria, EntityCollection } = Shopware.Data;

Shopware.Component.register('sw-cms-el-product-stream-preview-aku-cms-factory', {
    template,
    props: {
        product_stream_id: {
            type: String
        },

    },
    inject: ['repositoryFactory'],
    data(){
        return {
            product_stream: null,
        }
    },
    computed: {
        productStreamRepository() {
            return this.repositoryFactory.create('product_stream');
        },
    },
    methods: {
        loadData(){
            let that = this;
            let uuidRegex = new RegExp('^[0-9a-fA-F]{32}$');
            this.product_stream = null;
            if (null == this.product_stream_id || !uuidRegex.test(String(this.product_stream_id))) {
                return;
            }
            this.productStreamRepository
                .get(this.product_stream_id, Shopware.Context.api)
                .then((result) => {
                    that.product_stream = result;
                });
        }
    },
    created() {
        this.loadData();
    },
    watch: {
        product_stream_id(){
            this.loadData();
        }
    }
});