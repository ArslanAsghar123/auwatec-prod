import template from './aku-cms-factory-element-list.html.twig';

const {Component, Mixin} = Shopware;
const {Criteria} = Shopware.Data;

Component.register('aku-cms-factory-element-list', {
    template,
    
    inject: [
        'repositoryFactory'
    ],
    data() {
        return {
            repository: null,
            cms_elements: null,
            isLoading: false,
        }
    },
    metaInfo(){
        return {
            title: this.$createTitle()
        }

    },
    computed: {
        columns() {
            return this.getColumns();
        },
        element_list_criteria(){
            return (new Criteria())
                .addSorting(Criteria.sort('name', 'ASC'));
        }

    },
    created() {
        this.createdComponent();
        

    }, 
    methods: {
        createdComponent() {
            this.repository = this.repositoryFactory.create('cms_factory_element')
            this.repository.search(this.element_list_criteria, Shopware.Context.api).then((result) => {
                this.cms_elements = result
            })
        },
        exportFieldsRecursive(fields) {
            let that = this;
            let export_fields = [];
            fields.forEach(function(field, i){
                let export_field = Object.assign({}, field)
                delete export_field["id"];
                delete export_field["parent_id"];
                delete export_field["children"];
                if (field.hasOwnProperty("children") && 0 < field["children"].length){
                    let children = that.exportFieldsRecursive(field.children);
                    export_field["children"] = children;
                } else {
                    export_field["children"] = []
                }
                export_fields.push(export_field);
            })
            return export_fields;
        },
        exportItem(row){
            let fields = JSON.parse(row.fields);
            let export_fields = this.exportFieldsRecursive(fields);
            let data = {
                name: row.name,
                fields: export_fields,
                twig: row.twig
            }
            let blob = new Blob([JSON.stringify(data, undefined, 2)],  {type: "application/json"});
            let link=document.createElement('a');
            link.href=window.URL.createObjectURL(blob);
            link.download= row.name + '.json';
            link.click();
            return
        },

        getColumns() {
            return [
                { 
                    property: 'name', 
                    label: 'Name', 
                    rawData: false, 
                    primary: true,
                    routerLink: 'aku.cms.factory.element.detail',
                }
            ]
        },

        onSearch(term){
            let new_criteria = (new Criteria())
                .addSorting(Criteria.sort('name', 'ASC'));
            if (term != ''){
                let parts = term.split(' ')
                parts.forEach(function(part){
                    new_criteria.addFilter(Criteria.contains('name', part));
                })
            }
            let that = this;
            this.repository.search(new_criteria, Shopware.Context.api).then((result) => {
                that.cms_elements = result
            })
        },

    }


})