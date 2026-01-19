import TemplateService from '../../../../aku-cms-factory-element/TemplateService';
import template from './sw-cms-el-aku-cms-factory.html.twig';
import  './sw-cms-el-aku-cms-factory.scss';

const { Component, Mixin } = Shopware;
const { Criteria, EntityCollection } = Shopware.Data;

Shopware.Component.register('sw-cms-el-aku-cms-factory', {
    template,
    mixins: [
        Mixin.getByName('cms-element')
    ],
    inject: ['repositoryFactory'],

    data(){
        return {
            cmsFactoryElementList: [],
            cmsFactoryElement: null,
            cmsFactoryElementLoading: false,
        }
    },
    computed: {
        current_state() {
            if (this.cmsFactoryElementLoading) {
                return 'loading';
            } else if (null !== this.cmsFactoryElement) {
                return 'element_defined';
            } else if (0 == this.cmsFactoryElementList.length){
                return 'no_elements';
            } else {
                return 'element_selection';
            }
        },
        elementRepository() {
            return this.repositoryFactory.create('cms_factory_element');
        },
        columns() {
            return [
                {
                    property: 'label',
                    label: this.$tc('aku-cms-factory-element.component.labelLabel'),
                }, 
                {
                    property: 'value',
                    label: this.$tc('aku-cms-factory-element.component.valueLabel'),
                }, 
            ]
        },
        dataSource() {
            if (!this.cmsFactoryElement) {
                return []
            }
            let fields = JSON.parse(this.cmsFactoryElement.fields)
            if (null === fields) {
                fields = []
            }
            let values = this.element.config.field_values 
                ? this.element.config.field_values.value
                : {};

            if ("string" == typeof(values)){
                try {
                    values = JSON.parse(values)
                } catch (err) {
                    values = {}
                }
            }

            this.preRender()
            return this.getValues(fields, values)
        },
        
        cmsFactoryElementId() {
            if (this.element.config && this.element.config.cms_factory_element_id) {
                return this.element.config.cms_factory_element_id.value;
            } else {
                return ''
            }
        },
        elementor() {
            return this.element;
        },
        
    },
    created() {
        this.createdComponent();
    },
    methods: {
        createdComponent() {
            this.initElementConfig('aku-cms-factory');
            this.loadCmsFactoryElement();
        },
        loadCmsFactoryElementList(){
            let that = this;
            this.cmsFactoryElementLoading = true;
            this.elementRepository
                .search(new Criteria(), Shopware.Context.api)
                .then(function(result){
                    that.cmsFactoryElementList = result;
                    that.cmsFactoryElementLoading = false;
                })
                .catch(function(error) {
                    that.cmsFactoryElementLoading = false;
                });
        },
        loadCmsFactoryElement(){
            let that = this;
            if (this.cmsFactoryElementId) {
                this.cmsFactoryElementLoading = true;
                this.elementRepository
                    .get(this.cmsFactoryElementId, Shopware.Context.api, new Criteria())
                    .then((result) => {
                        that.cmsFactoryElement = result;
                        that.cmsFactoryElementLoading = false;
                        if (null == result){
                            this.loadCmsFactoryElementList();
                        }
                    }) 
                    .catch(function(error) {
                        that.cmsFactoryElementLoading = false;
                    });
            } else {
                this.cmsFactoryElement = null;
                // load the list
                this.loadCmsFactoryElementList();
            }

        },
        getChoiceLabel(field, value){
            let choices = [];
            let select_options = [];
            try {
                choices = JSON.parse(field.choices)
            } catch(e) {
                choices = [];
            }
            if (Array.isArray(choices)) {
                return value;
            } else {
                for (let label in choices) {
                    if (value == choices[label]) {
                        return label;
                    }
                }
            }

            // Nothing else found
            return value;
    
        },
        getValues(fields, values, name_prefix) {
            let that = this;
            let data = [];
            if (typeof(name_prefix) == 'undefined') {
                name_prefix = '';
            }
            fields.forEach(function(field){
                let defaultValue = field.defaultValue;
                let value = values.hasOwnProperty(field.name)
                    ? values[field.name]
                    : defaultValue;
                if ('repeater' == field.type) {
                    for (var i=0; i<value.length;i++) {
                        let subvals = value[i];
                        let sub_name_prefix = `${name_prefix}${field.label}[${i}].`;
                        let subrows = that.getValues(field.children, subvals, sub_name_prefix)
                        subrows.forEach(function(row){
                            data.push(row)
                        })
                    }
                }else if( field.type === "custom-entity"){
                    data.push({
                        name: name_prefix + field.name,
                        label: name_prefix + field.label,
                        value: value,
                        type: field.type,
                        entity:field.entity,
                        entityLabelProperty:field.entityLabelProperty
                    })
                } else {
                    if ('choice' == field.type) {
                        value = that.getChoiceLabel(field, value);
                    }
                    data.push({
                        name: name_prefix + field.name,
                        label: name_prefix + field.label,
                        value: value,
                        type: field.type,
                    })
                }
            })
            return data;
        },
        async preRender() {
            let fields = [];
            let values = {};
            let template = '';
            if (this.cmsFactoryElement) {
                template = this.cmsFactoryElement.twig;
                fields = JSON.parse(this.cmsFactoryElement.fields)
                if (null === fields) {
                    fields = []
                }
                values = this.element.config.field_values 
                    ? this.element.config.field_values.value
                    : {};
            }
        },
        selectCmsFactoryElement(e){
            this.element.config.cms_factory_element_id.value = e.id;
        }

    }, 
    watch: {
        cmsFactoryElementId() {
            let new_id = this.cmsFactoryElementId;
            if (new_id
                && !this.cmsFactoryElementLoading 
                && (
                    !this.cmsFactoryElement 
                    || this.cmsFactoryElement.id != new_id
                )
            ) {
                this.loadCmsFactoryElement();
            }
            
        }
    }
});