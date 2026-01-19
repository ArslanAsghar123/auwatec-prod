import template from './aku-cms-factory-element-edit.html.twig';
import './aku-cms-factory-element-edit.scss';
import TemplateService from './../../TemplateService.js';
import CmsElementService from './../../CmsElementService.js';


const {Component, Mixin, Utils} = Shopware;

Component.register('aku-cms-factory-element-edit', {
    template,

    inject: [
        'repositoryFactory'
    ],
    mixins: [
        Mixin.getByName('notification')
    ],
    metaInfo() {
        return {
            title: this.$createTitle()
        }
    },
    data(){
        return {
            element: null,
            isLoading: false,
            processSuccess: false,
            repository: null,
            field: null, // currently edited field
            fields: [],
            errors: { },
            showSampleCode: false,
            confirm_delete_item: null,
        }
    },
    created() {
        this.createdComponent();
    },
    computed: {
        gridItems() {
            let items = [];
            let that = this;
            let fields = this.fields;
            fields.forEach(function(parent, i){
                let parent_item = {
                    id: parent.id,
                    parentItem: null,
                    label: parent.label,
                    name: parent.name,
                    type: parent.type,
                    isFirst: i == 0,
                    isLast: i == (fields.length - 1),
                    currentPos: i
                }
                items.push(parent_item);
                if (Array.isArray(parent.children)) {
                    parent.children.forEach(function(child, j){
                        items.push({
                            id: child.id,
                            parentItem: parent_item, 
                            label: child.label,
                            name: child.name,
                            type: child.type,
                            isFirst: j == 0,
                            isLast: j == (parent.children.length - 1),
                            currentPos: j
                        })

                    })
                }

            })

            return items;
        },
    },
    methods: {       
        closedModal() {
            this.field = null
        },
        createdComponent(){
            this.repository = this.repositoryFactory.create('cms_factory_element')
            this.getCmsFactoryElement();
        },
        getCmsFactoryElement(){
            this.resetErrors();
            this.repository.get(this.$route.params.id, Shopware.Context.api).then((entity) => {
                this.element = entity
                this.fields = [];
                if (null != entity.fields){
                    try{
                        let fields = JSON.parse(this.element.fields)
                        this.fields = fields;
                    } catch(exception) {
                        // Nothing to do
                    }
                }
            })
        },
        gridItemsRecursive(fields, parentItem) {
            let items = [];
            let that = this;
            if (undefined === fields){
                fields = this.fields;
            }
            if (undefined === parentItem){
                parentItem = null;
            }
            fields.forEach(function(item, i){
                let name = parentItem ? parentItem.name + '[0]' : '';
                name+= item.name;
                let grid_item = {
                    id: item.id,
                    parentItem: parentItem,
                    level: parentItem ? parentItem.level+1 : 0,
                    label: item.label,
                    name: name,
                    type: item.type,
                    isFirst: i == 0,
                    isLast: i == (fields.length - 1),
                    currentPos: i
                }
                items.push(grid_item);
                if (Array.isArray(item.children)) {
                    let append_items = that.gridItemsRecursive(item.children, grid_item);
                    items.push(...append_items)
                }

            })

            return items;
        },
        move(field_id, new_pos, parent_id){
            let current_list = [];
            let new_list = [];
            let parent_field = null;
            let idx_of_parent_field = 0;
            let field_to_move = null;
            if (!parent_id) {
                current_list = this.fields;
            } else {
                this.fields.forEach(function(field, idx){
                    if (field.id == parent_id) {
                        parent_field = field;
                        idx_of_parent_field = idx;
                        current_list = field.children;
                    }
                });
            }
            if (0 > new_pos || new_pos > current_list.length - 1) {
                // Do not move outside of list
                return;
            }
            field_to_move = current_list.filter(function(field){
                return field.id == field_id;
            })[0];
            if (!field_to_move) {
                console.log('Error: field not found');
                return;
            }
            current_list = current_list.filter(function(field){
                return field.id != field_id;
            })
            
            for (var i = 0; i < current_list.length; i++) {
                if (i == new_pos) {
                    new_list.push(field_to_move);
                }
                new_list.push(current_list[i]);
            }
            if (new_pos > (current_list.length - 1)) {
                // Anhängen wenn an letzte Position
                new_list.push(field_to_move);
            }

            if (!parent_id) {
                this.fields = new_list;
            } else {
                parent_field.children = new_list;
                let new_fields = this.fields;
                new_fields[idx_of_parent_field] = parent_field;
                this.fields = new_fields;
            }
        },
        async onClickSave(){
            let that = this;
            this.element.fields = JSON.stringify(this.fields);
            this.errors = {}
            let new_errors = CmsElementService.validate(this.element, this.fields, this);
            if (0 < Object.keys(new_errors).length) {
                this.errors = new_errors;
                return;
            }
            
            this.isLoading = true;
            try {
                await TemplateService.renderTemplate(this.element.twig);
            } catch(err) {
                let error = '';
                if ('response' in err && 'data' in err.response && 'error' in err.response.data) {
                    error = err.response.data.error;
                } else {
                    error = err;
                }
                error = this.$tc('aku-cms-factory-element.edit.errorRender') + ': ' + error;
                this.errors = {
                    "twig_render": {
                        "detail": error
                    }
                }
                this.isLoading = false;
                return;
            }
            
            this.repository.save(this.element, Shopware.Context.api).then(() => {
                this.getCmsFactoryElement()
                this.isLoading = false
                this.processSuccess = true

            }).catch((exception) => {
                this.isLoading = false
                this.createNotificationError({
                    title: this.$tc('aku-cms-factory-element.edit.errorTitle'),
                    message: exception
                })

            })
        },

        resetErrors() {
            this.errors['name'] = null;
        },

        saveFinished(){
            this.processSuccess = false
        },


        fieldAdd(parentTreeItem) {
            let new_field = {
                id: '',
                parent_id: 'undefined' == typeof(parentTreeItem.id) ? '' : parentTreeItem.id,
            } 
            this.field = new_field;
        },
        
        fieldSave(field) {
            let new_fields = [];
            let is_new = false
            if (!field.id) {
                field.id = Utils.createId();
                new_fields = this.fieldAppend(this.fields, '', field);
            } else {
                new_fields = this.fieldUpdate(this.fields, '', field);

            }
            this.fields = new_fields;
            this.field = null;
        },
        fieldUpdate(current_list, parent_id, changed_field){
            let that = this;
            let new_list = [];
            current_list.forEach(function(item){
                if (item.id == changed_field.id){
                    new_list.push(changed_field);
                } else if ('repeater' == item.type && Array.isArray(item.children)){
                    item.children = that.fieldUpdate(item.children, item.id, changed_field);
                    new_list.push(item);
                } else {
                    new_list.push(item);
                }
            })

            return new_list;
        },
        fieldAppend(current_list, parent_id, new_field){
            let that = this;
            let new_list = [];
            current_list.forEach(function(item){
                if ('repeater' == item.type){
                    if (!Array.isArray(item.children)){
                        item.children = []
                    }
                    item.children = that.fieldAppend(item.children, item.id, new_field)
                } 
                new_list.push(item);
            })
            if (new_field.parent_id == parent_id){
                new_list.push(new_field);
            }

            return new_list
        },

        getFieldById(id, fields) {
            let that = this;
            let field = null;
            fields.forEach(function(parent){
                if (parent.id == id) {
                    field = parent;
                }
                if (Array.isArray(parent.children)) {
                    let inner_field = that.getFieldById(id, parent.children);
                    if (inner_field){
                        field = inner_field;
                    }
                }
            })
            return field;
        },

        
        treeItemEdit(treeItem) {
            this.field = this.getFieldById(treeItem.id, this.fields);
        },

        treeItemDelete() {
            let treeItem = this.confirm_delete_item;
            let new_fields = [];
            this.fields.forEach(function(parent){
                if (parent.id != treeItem.id) {
                    let new_children = []
                    if (Array.isArray(parent.children)) {
                        parent.children.forEach(function(child){
                            if (child.id != treeItem.id) {
                                new_children.push(child)
                            }
                        })
                    }
                    parent.children = new_children;
                    new_fields.push(parent);
                }
            })
            this.fields = new_fields
            this.confirm_delete_item = null;
        },
        useSampleCode(){

            this.element.twig = TemplateService.getSampleTemplate(this.fields)

        }

        
    }, 
    watch: {
        element: function(){
            //console.log(this.element.twig)
        }
    }

})
