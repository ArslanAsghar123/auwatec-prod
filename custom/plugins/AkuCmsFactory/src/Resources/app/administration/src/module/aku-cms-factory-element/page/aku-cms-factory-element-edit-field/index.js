import template from './aku-cms-factory-element-edit-field.html.twig';

Shopware.Component.register('aku-cms-factory-element-edit-field', {
    template,

    props: [
        'field'
    ],
    data(){
        return {
            type: 'text',
            name: '',
            label: '',
            defaultValue: '',
            childrenDepth: 1,
            //inputRequired: false,
            choices: "",
            fieldMaxlength: null,
            //limit: 10,
            associations: [],
            associations_string:"",
            errors: {},
            show_confirm_delete: false,
            entity:"",
            entityLabelProperty:""
        }
    },
    computed: {
        type_choices(){
            let choices = [
                {
                    value: 'text',
                    label: this.$tc('aku-cms-factory-element.edit.typeText')
                },
                {
                    value: 'textarea',
                    label: this.$tc('aku-cms-factory-element.edit.typeTextarea')
                },
                {
                    value: 'text-editor',
                    label: this.$tc('aku-cms-factory-element.edit.typeTextEditor')
                },
                {
                    value: 'media',
                    label: this.$tc('aku-cms-factory-element.edit.typeMedia')
                },
                {
                    value: 'manufacturer',
                    label: this.$tc('aku-cms-factory-element.edit.typeManufacturer')
                },
                {
                    value: 'gallery',
                    label: this.$tc('aku-cms-factory-element.edit.typeGallery')
                },
                {
                    value: 'category',
                    label: this.$tc('aku-cms-factory-element.edit.typeCategory')
                },
                {
                    value: 'product',
                    label: this.$tc('aku-cms-factory-element.edit.typeProduct')
                },
                {
                    value: 'stream',
                    label: this.$tc('aku-cms-factory-element.edit.typeStream')
                },
                {
                    value: 'color',
                    label: this.$tc('aku-cms-factory-element.edit.typeColor')
                },
                {
                    value: 'choice',
                    label: this.$tc('aku-cms-factory-element.edit.typeChoice')
                },

                {
                    value: 'checkbox',
                    label: this.$tc('aku-cms-factory-element.edit.typeCheckbox')
                },
                {
                    value: 'custom-entity',
                    label: this.$tc('aku-cms-factory-element.edit.typeCustomEntity')
                },
            ];
            // if ('' == this.field.parent_id) {
            choices.push({
                value: 'repeater',
                label: this.$tc('aku-cms-factory-element.edit.typeRepeater')
            });
            //}

            return choices;
        },
        allow_choices() {
            return 'choice' == this.type;
        },
        allow_default_value() {
            return ['media', 'gallery', 'manufacturer','custom-entity','repeater', 'category', 'product', 'color', 'stream', 'checkbox'].indexOf(this.type)===-1;
        },
        allow_input_required() {
            return -1 < ['text', 'textarea'].indexOf(this.type);
        },
        allow_children_depth() {
            return (-1 < ['category'].indexOf(this.type) 
                && -1 < this.associations.indexOf('children'))
        },
        name_pattern() {
            if ('category' == this.type) {
                return "cat[a-z0-9_]*"
            }
            if ('choice' == this.type) {
                return "choice[a-z0-9_]*"
            }
            if ('checkbox' == this.type) {
                return "bool[a-z0-9_]*"
            }
            if ('color' == this.type) {
                return "color[a-z0-9_]*"
            }
            if ('media' == this.type) {
                return "med[a-z0-9_]*"
            }
            if ('manufacturer' == this.type) {
                return "man[a-z0-9_]*"
            }
            if ('custom-entity' == this.type) {
                return "cus[a-z0-9_]*"
            }
            if ('product' == this.type) {
                return "prod[a-z0-9_]*"
            }
            if ('stream' == this.type) {
                return "stream[a-z0-9_]*"
            }
            if ('gallery' == this.type) {
                return "gallery[a-z0-9_]*"
            }
            if ('repeater' == this.type) {
                return "rep[a-z0-9_]*"
            }
            return "text[a-z0-9_]*";
        },
        association_choices(){
            let that = this;
            let choices = [];
            let associations = [];
            if ('category' == this.type){
                associations = [
                    'media', 'custom_fields', 
                    // Unterkategorien
                    'children', 'children.media', 'children.custom_fields',
                    //Produkte
                    'products', 'products.cover', 'products.media', 'products.custom_fields',
                    //Produktvarianten
                    'products.children', 'products.children.cover', 'products.children.media', 'products.children.custom_fields',
                ];
            }

            if ('media' == this.type) {
                associations = [
                    'custom_fields', 
                ];
            }
            if ('manufacturer' == this.type) {
                associations = [
                    'media',
                    'custom_fields', 
                ];
            }
            if ('gallery' == this.type) {
                //console.log('gallery')
                associations = [
                    'custom_fields', 
                ];
            }

            if ('product' == this.type){
                associations = [
                    'cover' ,'media', 'custom_fields', 'properties', 
                    'children', 'children.cover', 'children.media', 'children.custom_fields',
                    'crossSellings.assignedProducts', 'crossSellings.assignedProducts.product.cover', 'crossSellings.assignedProducts.product.media', 'crossSellings.assignedProducts.product.custom_fields'
                    
                ];
            }
            associations.forEach(function(assoc){
                // Punkt geht nicht
                let trans_key = assoc.split('.').join('_');
                if ('product' == that.type){
                    trans_key = 'product_' + trans_key;
                } else if ('category' == that.type){
                    trans_key = 'category_' + trans_key;
                } else if ('media' == that.type || 'gallery' == that.type){
                    trans_key = 'media_' + trans_key;
                } else if ('manufacturer' == that.type){
                    trans_key = 'manufacturer_' + trans_key;
                }

                choices.push({
                    value: assoc,
                    label:  that.$tc('aku-cms-factory-element.edit.association_' + trans_key)
                });
            })

            return choices;
        },
        default_value_choices: function(){
            if ('checkbox' == this.type){
                return [
                    {name: this.$tc('aku-cms-factory-element.edit.label_unchecked'), value: false},
                    {name: this.$tc('aku-cms-factory-element.edit.label_checked'), value: true}
                ]
            } else {
                return [];
            }
        }


    },
    methods: {
        save() {
            let new_errors = {
                name: null,
                label: null,
                choices: null,
            };
            let name_regex = new RegExp('^' + this.name_pattern + '$');
            if ('' == this.name.trim()) {
                new_errors.name = {
                    detail: this.$tc('aku-cms-factory-element.edit.inputRequired')
                }
            } else if (!name_regex.test(this.name)) {
                if ('media' == this.type) {
                    new_errors.name = {
                        detail: this.$tc('aku-cms-factory-element.edit.fieldNameRulesErrorMedia')
                    }
                } else if ('manufacturer' == this.type) {
                    new_errors.name = {
                        detail: this.$tc('aku-cms-factory-element.edit.fieldNameRulesErrorManufacturer')
                    }
                }else if ('custom-entity' == this.type) {
                        new_errors.name = {
                            detail: this.$tc('aku-cms-factory-element.edit.fieldNameRulesErrorCustomEntity')
                        }
                } else if ('gallery' == this.type) {
                    new_errors.name = {
                        detail: this.$tc('aku-cms-factory-element.edit.fieldNameRulesErrorGallery')
                    }
                } else if ('checkbox' == this.type) {
                    new_errors.name = {
                        detail: this.$tc('aku-cms-factory-element.edit.fieldNameRulesErrorCheckbox')
                    }
                } else if ('category' == this.type) {
                    new_errors.name = {
                        detail: this.$tc('aku-cms-factory-element.edit.fieldNameRulesErrorCategory')
                    }
                } else if ('repeater' == this.type) {
                    new_errors.name = {
                        detail: this.$tc('aku-cms-factory-element.edit.fieldNameRulesErrorRepeater')
                    }
                } else if ('product' == this.type) {
                    new_errors.name = {
                        detail: this.$tc('aku-cms-factory-element.edit.fieldNameRulesErrorProduct')
                    }
                } else if ('color' == this.type) {
                    new_errors.name = {
                        detail: this.$tc('aku-cms-factory-element.edit.fieldNameRulesErrorColor')
                    }
                } else if ('choice' == this.type) {
                    new_errors.name = {
                        detail: this.$tc('aku-cms-factory-element.edit.fieldNameRulesErrorChoice')
                    }
                } else if ('stream' == this.type) {
                    new_errors.name = {
                        detail: this.$tc('aku-cms-factory-element.edit.fieldNameRulesErrorStream')
                    }
                } else {
                    new_errors.name = {
                        detail: this.$tc('aku-cms-factory-element.edit.fieldNameRulesErrorText')
                    }
                }
            }
            if ('' == this.label.trim()) {
                new_errors.label = {
                    detail: this.$tc('aku-cms-factory-element.edit.inputRequired')
                }
            }
            if ('choice' == this.type) {
                if ('' == this.choices.trim()) {
                    new_errors.choices = {
                        detail: this.$tc('aku-cms-factory-element.edit.inputRequired')
                    }
                } else {
                    try {
                        let choices = JSON.parse(this.choices);
                    } catch (e) {
                        new_errors.choices = {
                            detail: this.$tc('aku-cms-factory-element.edit.fieldChoiceRulesErrorJson')
                        }
                    }
                }
            }
            for (let key in new_errors) {
                if (new_errors[key] != null) {
                    this.errors = new_errors;
                    return;
                }
            }
            let entity = null;
            let labelProperty = null;
            if(this.type === "custom-entity"){
                if(this.associations_string && this.associations_string.trim().length > 0){
                    this.associations = this.associations_string.split(",");
                }
                entity = this.entity;
                labelProperty = this.entityLabelProperty;
            }
            this.$emit('saved', {
                id: this.field.id,
                parent_id: this.field.parent_id,
                children: 'repeater' == this.type && Array.isArray(this.field.children)
                    ? this.field.children
                    : [],
                type: this.type,
                name: this.name.trim(),
                label: this.label,
                choices: this.choices,
                defaultValue: this.defaultValue,
                associations: this.associations,
                fieldMaxlength: this.fieldMaxlength,
                //limit: this.limit,
                childrenDepth: this.childrenDepth,
                entity:entity,
                entityLabelProperty:labelProperty
                //inputRequired: this.inputRequired,

            })
        },

        resetErrors() {
            this.errors = {
                name: null,
                label: null,
                choices: null,
            }
        },
        load() {
            this.resetErrors();
            this.type = this.field.hasOwnProperty('type') ? this.field.type : 'text';
            this.name = this.field.hasOwnProperty('name') ? this.field.name : '';
            this.label = this.field.hasOwnProperty('label') ? this.field.label : '';
            this.choices = this.field.hasOwnProperty('choices') ? this.field.choices : '';
            this.defaultValue = this.field.hasOwnProperty('defaultValue') ? this.field.defaultValue :'';
            this.associations = this.field.hasOwnProperty('associations') ? this.field.associations : [];
            this.fieldMaxlength = this.field.hasOwnProperty('fieldMaxlength') ? this.field.fieldMaxlength : null;
            this.childrenDepth = this.field.hasOwnProperty('childrenDepth') ? this.field.childrenDepth : 1;
            this.entity = this.field.hasOwnProperty('entity') ? this.field.entity : null;
            this.entityLabelProperty = this.field.hasOwnProperty('entityLabelProperty') ? this.field.entityLabelProperty : null;
            this.associations_string = this.field.hasOwnProperty('associations') ? this.field.associations.join(",") : null;
            //this.inputRequired = this.field.hasOwnProperty('inputRequired') ? this.field.inputRequired : false;
        }
    },
    watch: {
        field(){
            this.load()
        }, 
    },
    created() {
        
        this.load()

    }

})