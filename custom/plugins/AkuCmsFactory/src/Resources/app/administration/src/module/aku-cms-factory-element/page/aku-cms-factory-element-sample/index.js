import template from './aku-cms-factory-element-sample.html.twig';
import TemplateService from './../../TemplateService.js';
const {Component, Mixin} = Shopware;
Component.register('aku-cms-factory-element-sample', {
    template,
    props: ['fields'],
    computed: {
        code() {
            let code = ''
            let that = this;
            if (0 == this.fields.length) {
                return this.$tc('aku-cms-factory-element.edit.noFields');
            }
            code = TemplateService.getSampleTemplate(this.fields)
            return code;
        }
    },
})
