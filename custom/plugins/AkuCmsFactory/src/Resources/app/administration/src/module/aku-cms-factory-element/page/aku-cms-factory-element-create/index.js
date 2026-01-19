const {Component} = Shopware;
import TemplateService from './../../TemplateService.js';
import CmsElementService from './../../CmsElementService.js';

Component.extend('aku-cms-factory-element-create', 'aku-cms-factory-element-edit', {
    methods: {
        getCmsFactoryElement(){
            this.element = this.repository.create(Shopware.Context.api);
        },

        async onClickSave() {
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
        }
    }
})