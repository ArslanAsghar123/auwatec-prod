import template from './sw-cms-el-aku-cms-factory-config-repater.html.twig';
import './sw-cms-el-aku-cms-factory-config-repeater.scss';

/* The repeater Component implements functionality to
- add a repeater fieldgroup
- remove a repeated fieldgroup
- move repeated fieldgroups
- save data from a field group and  
  emit all data to parent config component
*/
Shopware.Component.register('sw-cms-el-aku-cms-factory-config-repeater', {
    template,
    props: {
        label: {
            type: String,
            required: false,
            default: ''
        },
        // current config data of all fieldgroups

        value: {
            type: Array,
            required: false,
            default: []
        },
        fields: {
            type: Array,
            required: true,
            default: []
        },
        
    },
    data: function(){
        return {
            confirm_delete_idx: null,
            new_values:[]
        };
    },
    computed: {
        getValue(){
            let that = this;
            return function(idx){
                return that.value[idx]
            }
        }
    },
    methods: {
        save(new_values) {
            this.$emit('input', new_values)
            this.$emit('update', new_values)
            this.$emit('change', true)
        },
        saveFieldgroup(idx, args){
            let new_values = [];
            for(var i=0; i< this.value.length; i++) {
                if (i == idx) {
                    new_values.push(args)
                } else {
                    console.log(this.value);
                    let value = Object.assign({}, this.value[i]);
                    new_values.push(value)
                }
            }
            this.save(new_values)
            
        },
        addFieldGroup(){
            let new_values = this.value;
            new_values.push({})
            this.save(new_values)
        },
        moveFieldGroup(old_idx, new_idx)
        {
            if (old_idx == new_idx 
                || 0 > new_idx
                || new_idx >= this.value.length
            ) {
                return;
            }
            let new_values = [];
            for(var i=0; i< this.value.length; i++) {
                if (i != old_idx) {
                    if (i == new_idx && new_idx < old_idx){
                        new_values.push(this.value[old_idx])
                    }
                    new_values.push(this.value[i])
                    if (i == new_idx && new_idx > old_idx){
                        new_values.push(this.value[old_idx])
                    }
                }
            }
            this.save(new_values)

        },
        deleteFieldGroup(idx) {
            let new_values = []
            for(var i=0; i< this.value.length; i++) {
                if (i != idx) {
                    new_values.push(this.value[i])
                }
            }
            this.save(new_values)
        }
    },
    watch: {
        value(){
        }
    }
});