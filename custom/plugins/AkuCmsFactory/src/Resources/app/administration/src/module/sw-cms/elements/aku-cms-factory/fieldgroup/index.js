import template from './sw-cms-el-aku-cms-factory-config-fieldgroup.html.twig';
import './sw-cms-el-aku-cms-factory-config-fieldgroup.scss';

const {Component, Mixin} = Shopware;
const {Criteria, EntityCollection} = Shopware.Data;

Shopware.Component.register('sw-cms-el-aku-cms-factory-config-fieldgroup', {
    template,
    props: [
        'fields', // array of fields
        'value', // value object {fieldName: value}
        'defaultMediaFolderId',
        'entityName'
    ],
    data() {
        return {
            test: '#fff',
            configValues: {},
            mediaModalIsOpen: false,
            currentMediaField: null,
        }
    },
    inject: ['repositoryFactory'],
    computed: {
        productSelectContext() {
            const context = Object.assign({}, Shopware.Context.api);
            context.inheritance = true;

            return context;
            /*
            return {
                ...Shopware.Context.api,
                inheritance: true
            };
            */
        },

        productCriteria() {
            const criteria = new Criteria();
            criteria.addAssociation('options.group');
            return criteria;
        },
        mediaRepository() {
            return this.repositoryFactory.create('media');
        },
        categoryRepository() {
            return this.repositoryFactory.create('category');
        },
        manufacturerRepository() {
            return this.repositoryFactory.create('product_manufacturer');
        },
        manufacturerCriteria() {
            const criteria = new Criteria();
        },
        created() {
            this.createdComponent();
        },
        sorting_choices() {
            let choices = [
                {
                    value: 'name:ASC',
                    label: this.$tc('sw-cms.element.akuCmsFactory.config.sorting_name_asc')
                },
                {
                    value: 'name:DESC',
                    label: this.$tc('sw-cms.element.akuCmsFactory.config.sorting_name_desc')
                },
                {
                    value: 'createdAt:ASC',
                    label: this.$tc('sw-cms.element.akuCmsFactory.config.sorting_created_at_asc')
                },
                {
                    value: 'createdAt:DESC',
                    label: this.$tc('sw-cms.element.akuCmsFactory.config.sorting_created_at_desc')
                },
                {
                    value: 'cheapestPrice:ASC',
                    label: this.$tc('sw-cms.element.akuCmsFactory.config.sorting_price_asc')
                },
                {
                    value: 'cheapestPrice:DESC',
                    label: this.$tc('sw-cms.element.akuCmsFactory.config.sorting_price_desc')
                }
                /*,
                {
                    value: 'random:ASC',
                    label: this.$tc('sw-cms.element.akuCmsFactory.config.sorting_random')
                }*/
            ]
            return choices
        }
    },
    methods: {
        customEntityRepository(entityName) {
            return this.repositoryFactory.create(entityName);
        },
        getFieldMaxlength(field) {
            if (field.hasOwnProperty('fieldMaxlength')
                && null != field.fieldMaxlength
                && undefined != field.fieldMaxlength
                && !isNaN(field.fieldMaxlength)
            ) {
                return field.fieldMaxlength.toString()
            }
            return '';
        },
        getFieldRequired(field) {
            if (field.hasOwnProperty('inputRequired')) {
                return field.inputRequired;
            }
            return false;
        },
        createdComponent() {
            //this.presetConfigValues();
        },
        saveSingleId(evt,name){
            this.configValues[name]=evt;
            this.save();
        },
        saveMultiId(evt,name){
            this.configValues[name]=evt;
            this.save();
        },
        save(evt) {
            let configValues = Object.assign({}, this.configValues);
            this.$emit('input', configValues);
        },
        presetConfigValues() {
            let configValues = {}
            let currentValues = this.value;
            for (var i = 0; i < this.fields.length; i++) {
                let name = this.fields[i].name;
                let defaultValue = this.fields[i].defaultValue;
                let currentValue = currentValues.hasOwnProperty(name)
                    ? currentValues[name]
                    : defaultValue;
                if ('repeater' == this.fields[i].type && !Array.isArray(currentValue)) {
                    currentValue = [];
                }
                if ('manufacturer' == this.fields[i].type && !Array.isArray(currentValue)) {
                    currentValue = [];
                }
                if ('custom-entity' == this.fields[i].type && !Array.isArray(currentValue)) {
                    currentValue = [];
                }
                configValues[name] = currentValue;

                // streams haben limit
                if ('stream' == this.fields[i].type) {
                    let limit_field_name = this.fields[i].name + '_limit'
                    let limit_default_value = 1;
                    let limit_current_value = currentValues.hasOwnProperty(limit_field_name)
                        ? currentValues[limit_field_name]
                        : limit_default_value;
                    configValues[limit_field_name] = limit_current_value;
                    let sorting_field_name = this.fields[i].name + '_sorting'
                    let sorting_default_value = 'name:ASC';
                    let sorting_current_value = currentValues.hasOwnProperty(sorting_field_name)
                        ? currentValues[sorting_field_name]
                        : sorting_default_value;
                    configValues[sorting_field_name] = sorting_current_value;
                }
            }
            this.configValues = configValues;
        },
        onImageRemove(evt) {
            this.configValues[this.currentMediaField.name] = '';
            this.save();
            this.mediaModalIsOpen = false;
        },
        onSelectionChanges(mediaEntity) {
            let media = mediaEntity[0];
            if (media && media.id) {

                this.configValues[this.currentMediaField.name] = media.id;
            } else {
                this.configValues[this.currentMediaField.name] = '';
            }
            this.save();
            this.mediaModalIsOpen = false;
        },
        openMediaModal(field) {
            this.currentMediaField = field;
            this.mediaModalIsOpen = true;

        },
        removeMediaFromField(field) {
            this.configValues[field.name] = '';
            this.save();
        }
    },
    watch: {
        fields() {
            this.presetConfigValues();
        },
        value() {
            this.presetConfigValues();
        }
    },
    created() {
        this.presetConfigValues();

    }
})