const { Component, Mixin } = Shopware;

Component.extend('aku-cms-factory-textarea-field', 'sw-textarea-field', {
    props: {
        maxlength: {
            type: String,
            required: false,
            default() {
                return '';
            }
        }
    },

    methods: {
        getMaxlength(){
            return this.maxlength && !isNaN(this.maxlength)
                ? parseInt(this.maxlength, 10)
                : -1;
        },
        
        getSanitizedValue(val){
            let maxlength =  this.getMaxlength();
            return -1 < maxlength
                ? val.substring(0, maxlength)
                : val;
        },
        
        onChange(event) {
            this.$emit('update:value', this.getSanitizedValue(event.target.value || ''));
        },

        onInput(event) {
            event.target.value = this.getSanitizedValue(event.target.value)
            this.$emit('update:value', this.getSanitizedValue(event.target.value));
        },

    }
});
