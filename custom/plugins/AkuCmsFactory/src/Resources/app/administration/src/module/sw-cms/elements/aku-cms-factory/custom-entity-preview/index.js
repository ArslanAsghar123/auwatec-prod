import template from './sw-cms-custom-entity-preview-aku-cms-factory.html.twig';

const {Component, Mixin} = Shopware;
const {Criteria, EntityCollection} = Shopware.Data;

Shopware.Component.register('sw-cms-el-custom-entity-preview-aku-cms-factory', {
    template,
    props: {
        entityIds: {
            type: Array
        },
        labelProperty: {
            type: String
        },
        entityName:{
            type:String
        }
    },
    inject: ['repositoryFactory'],
    data(){
        return {
            entities: null,
        }
    },
    computed: {
        entityRepository() {
            return this.repositoryFactory.create(this.entityName);
        },
        entityCriteria() {
            const criteria = new Criteria();
            criteria.setIds(this.entityIds);
            return criteria;
        },
        entitySelectContext() {
            const context = Object.assign({}, Shopware.Context.api);
            context.inheritance = true;
            return context;
        },
    },
    methods: {
        async loadData(){
            this.entities = null;
            if (null == this.entityIds || !Array.isArray(this.entityIds) ||this.entityIds.length <= 0) {
                return;
            }
            this.entityRepository
                .search(this.entityCriteria, this.entitySelectContext)
                .then((result) => {
                    this.entities = result;
                });
        },

    },
    created() {
        this.loadData();
    },
    watch: {
        entityIds(){
            this.loadData();
        }
    }
});