import template from './aku-cms-factory-element-multicheckbox.html.twig';

Shopware.Component.register('aku-cms-factory-element-multicheckbox', {
    template,

    props: [
        'label',
        'helpText',
        'value',
        'options'
    ],
    data: function(){
        return {
            model: []
        }
    },
    methods: {
        setModel: function(){
            let new_model = [];
            let that = this;
            this.options.forEach(function(item){
                new_model.push({
                    label: item.label,
                    key: item.value,
                    checked: -1 < that.value.indexOf(item.value)
                })
            })
            this.model = new_model;
        },
        save: function(){
            let new_value = [];
            this.model.forEach(function(item){
                if (item.checked){
                    new_value.push(item.key);
                }
            });
            console.log(new_value);
            this.$emit('update:value', new_value);
        }
    },
    created: function(){
        this.setModel();
    }, 
    watch: {
        options: function(){
            this.setModel();
        },
        model:function(){
            this.save();
        }
    }
})