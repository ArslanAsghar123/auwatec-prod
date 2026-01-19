import template from './sw-cms-el-cbax-lexicon-text.html.twig';
import './sw-cms-el-cbax-lexicon-text.scss';

const { Component, Mixin } = Shopware;

Component.register('sw-cms-el-cbax-lexicon-text', {
    template,

    mixins: [
        Mixin.getByName('cms-element')
    ],

    data() {
        return {
            editable: true,
            demoValue: ''
        };
    },

    watch: {
        cmsPageState: {
            deep: true,
            handler() {
                this.updateDemoValue();
            }
        },

        'element.config.content.source': {
            handler() {
                this.updateDemoValue();
            }
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('cbax-lexicon-text');
        },

        updateDemoValue() {
            if (this.element.config.content.source === 'mapped') {
                this.demoValue = this.getDemoValue(this.element.config.content.value);
            }
        },

        onBlur(content) {
            this.emitChanges(content);
        },

        onInput(content) {
            this.emitChanges(content);
        },

        emitChanges(content) {
            if (content !== this.element.config.content.value) {
                this.element.config.content.value = content;
                this.$emit('element-update', this.element);
            }
        }
    }
});
