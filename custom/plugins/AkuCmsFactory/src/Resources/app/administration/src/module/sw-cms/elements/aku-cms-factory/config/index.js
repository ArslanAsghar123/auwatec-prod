import template from './sw-cms-el-config-aku-cms-factory.html.twig';
import './sw-cms-el-config-aku-cms-factory.scss';

const { Component, Mixin } = Shopware;
const { Criteria, EntityCollection } = Shopware.Data;

Shopware.Component.register('sw-cms-el-config-aku-cms-factory', {

    template,
    data() {
        return {
            cmsFactoryElement : null,
            loading: false,
            current_values: {},
        }
    },

    mixins: [
        Mixin.getByName('cms-element')
    ],

    inject: ['repositoryFactory'],
    
    computed: {
        current_state() {
            let current_state = '';
            if (this.loading) {
                current_state = 'loading';
            } else if (null == this.cmsFactoryElement) {
                current_state = 'no_element';
            } else {
                current_state = 'element_found';
            }
            return current_state;
        },
        element_list_criteria() {
            let criteria = new Criteria();
            criteria.addSorting(Criteria.sort('name', 'ASC'));
            return criteria;
        },
        fields() {
            let cmsFactoryElement = this.cmsFactoryElement;
            if (null == cmsFactoryElement || null == cmsFactoryElement.fields) {
                return [];
            }
            try {
                let fields = JSON.parse(cmsFactoryElement.fields);
                let field_mapper = function(field){
                    if ('choice' == field.type) {
                        let choices = [];
                        let select_options = [];
                        try {
                            choices = JSON.parse(field.choices)
                        } catch(e) {
                            choices = [];
                        }
                        if (Array.isArray(choices)) {
                            choices.forEach(function(choice){
                                select_options.push({
                                   label: choice,
                                   value: choice 
                                });
                            });
                        } else if (typeof choices == 'object') {
                            let keys = Object.keys(choices);
                            keys.forEach(function(key){
                                select_options.push({
                                    label: choices[key],
                                    value: key
                                });
                            })
                        }
                        field['select_options'] = select_options;
                    } 
                    if ('repeater' == field.type) {
                        field.children = field.children.map(field_mapper);
                    }
                    return field;
                }
                fields = fields.map(field_mapper);
                return fields;
            } catch(exception) {
                return []
            }
        },
        elementRepository() {
            return this.repositoryFactory.create('cms_factory_element');
        },
        cmsFactoryElementId(){
            return this.element.config.cms_factory_element_id.value;
        }
    },
    created() {
        this.createdComponent();
    },
    

    methods: {

        async createdComponent() {
            await this.initElementConfig('aku-cms-factory');
            this.loadCmsFactoryElement();
        },
        loadCmsFactoryElement(){
            let that = this;
            that.loading = true;
            if (this.element.config.cms_factory_element_id.value) {
                this.elementRepository
                    .get(this.element.config.cms_factory_element_id.value, Shopware.Context.api, new Criteria())
                    .then((result) => {
                        that.cmsFactoryElement = result;
                        that.loading = false;
                        if ("object" == typeof(that.element.config.field_values.value)) {
                            // legacy
                            that.current_values = Object.assign({}, that.element.config.field_values.value);
                        } else {
                            // Config als String speichern (ab 1.3.0)
                            try {
                                that.current_values = JSON.parse(that.element.config.field_values.value)
                            } catch (err) {
                                // pass
                            }
                        }
                        // Values zurück schreiben - scheint mir unnötig?
                        // that.element.config.field_values.value = that.current_values;

                    })
                    .catch(function(error){
                        that.loading = false;
                    });
            } else {
                that.cmsFactoryElement = null;
                that.loading = false;
            }

        },
        updateFieldValues(values){
            let new_values = Object.assign({}, values);
            //this.element.config.field_values.value = new_values;
            // Speichern als String. Dies löst das Problem
            // Layout - Speichern > Allgemein Speichern+Speichern 
            this.element.config.field_values.value = JSON.stringify(new_values);
            this.$emit('element-update', this.element);
        }

    },
    watch: {
        cmsFactoryElementId(){
            this.loadCmsFactoryElement();
        }
    }

});