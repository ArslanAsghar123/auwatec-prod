import template from './aku-cms-factory-element-import.html.twig';
import './aku-cms-factory-element-import.scss';
const {Component, Mixin, Utils} = Shopware;
import TemplateService from './../../TemplateService.js';
import CmsElementService from './../../CmsElementService.js';

Component.register('aku-cms-factory-element-import', {
    template,

    inject: [
        'repositoryFactory'
    ],
    mixins: [
        Mixin.getByName('notification')
    ],
    data: function(){
        return {
            errors: [],
            selected_file: null,
            processSuccess: false,
            import_content: '',
            isLoading: false,
        }
    },
    methods: {
        addFieldIdsRecursive: function(fields, parent_id){
            if (undefined == parent_id){
                parent_id = "";
            }
            let that = this;
            let new_fields = []
            fields.forEach(function(field){
                field["id"] = Utils.createId();
                field["parent_id"] = parent_id;
                if (field.hasOwnProperty("children") && Array.isArray(field["children"])){
                    field["children"] = that.addFieldIdsRecursive(field["children"]);
                } else {
                    field["children"] = []
                }
                new_fields.push(field);
            })
            return new_fields;
        },
        createdComponent(){
            this.repository = this.repositoryFactory.create('cms_factory_element')
            this.getCmsFactoryElement();
        },
        getCmsFactoryElement(){
            this.element = this.repository.create(Shopware.Context.api);
        },

        onClickSave: async function(){
            let that = this;
            let new_errors = []
            let json_content = null
            this.errors = [];
            if ('' == this.import_content.trim()){
                return
            }
            try {
                json_content = JSON.parse(this.import_content);
            } catch (error) {
                new_errors.push(error)
                this.errors = new_errors;
                return
            }
            if (!json_content.hasOwnProperty('name')
                ||  typeof(json_content.name) != "string"
                || json_content.name == ""
            ){
                new_errors.push(this.$tc("aku-cms-factory-element.import.importErrorPropertyNameMissing"))
            } 
            if (!json_content.hasOwnProperty('fields')
                || !Array.isArray(json_content.fields)
            ){
                new_errors.push(this.$tc("aku-cms-factory-element.import.importErrorPropertyFieldsMissing"))
            }  
            if (!json_content.hasOwnProperty('twig') 
                || typeof(json_content.twig) != "string"
                || json_content.twig.trim() == ""
            ){
                // unbedingt auf leerstring prüfen, weil CmsElementService wird sonst auch prüfen und eine seltsame Meldung geben.
                new_errors.push(this.$tc("aku-cms-factory-element.import.importErrorPropertyTwigMissing"))
            }
            if (0 < new_errors.length){
                // Basic errors - gleich abbrechen
                this.errors = new_errors;
                return;
            }
            this.element.name = json_content.name;
            this.element.twig = json_content.twig;
            let fieldsWithIds = this.addFieldIdsRecursive(json_content.fields);
            this.element.fields = JSON.stringify(fieldsWithIds);
            let validation_errors = CmsElementService.validate(this.element, fieldsWithIds, this);
            let validation_errors_keys = Object.keys(validation_errors)
            if (0 < validation_errors_keys.length){
                validation_errors_keys.forEach(function(key){
                    new_errors.push(key + " " + validation_errors[key]["detail"])
                })
            }
            this.isLoading = true;
            try {
                await TemplateService.renderTemplate(this.element.twig);
            } catch(err) {
                let parse_error = '';
                if ('response' in err && 'data' in err.response && 'error' in err.response.data) {
                    parse_error = err.response.data.error;
                } else {
                    parse_error = err;
                }
                parse_error = this.$tc('aku-cms-factory-element.edit.errorRender') + ': ' + parse_error;
                new_errors.push("twig " + parse_error)
            }
            if (0 < new_errors.length){
                this.errors = new_errors;
                this.isLoading = false; 
                return
            }
            this.repository.save(this.element, Shopware.Context.api).then((entity) => {
                that.isLoading = false;
                that.$router.push({name: 'aku.cms.factory.element.overview'})
            }).catch((exception) => {
                that.isLoading = false;
                that.createNotificationError({
                    title: that.$tc('aku-cms-factory-element.edit.errorSave'),
                    message: exception,
                })
            })
            
        },
        
    },
    created() {
        this.createdComponent();
    },
    watch: {
        selected_file: function(){
            let that = this;
            let reader = new FileReader();
            reader.onload = function(theFileData) {
                that.import_content = reader.result
            }
            reader.readAsText(this.selected_file);
        }
    }
})