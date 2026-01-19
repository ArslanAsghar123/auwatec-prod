import template from './sw-cms-category-preview-aku-cms-factory.html.twig';

const { Component, Mixin } = Shopware;
const { Criteria, EntityCollection } = Shopware.Data;

Shopware.Component.register('sw-cms-el-category-preview-aku-cms-factory', {
    template,
    props: {
        category_id: {
            type: String
        },

    },
    inject: ['repositoryFactory'],
    data(){
        return {
            category: null,
        }
    },
    computed: {
        categoryRepository() {
            return this.repositoryFactory.create('category');
        },
        breadcrumbs() {
            if (this.category && this.category.hasOwnProperty('breadcrumb') && Array.isArray( this.category.breadcrumb) && 0 < this.category.breadcrumb.length){
                return this.category.breadcrumb.join('/')
            } else if(this.category) {
                return this.category.name;
            } else {
                return '/'
            }
        }
    },
    methods: {
        loadCategory(){
            let that = this;
            let uuidRegex = new RegExp('^[0-9a-fA-F]{32}$');
            this.category = null;
            if (null == this.category_id || !uuidRegex.test(String(this.category_id))) {
                return;
            }
            this.categoryRepository
                .get(this.category_id, Shopware.Context.api)
                .then((result) => {
                    that.category = result;
                });
        }
    },
    created() {
        this.loadCategory();
    },
    watch: {
        category_id(){
            this.loadCategory();
        }
    }
});