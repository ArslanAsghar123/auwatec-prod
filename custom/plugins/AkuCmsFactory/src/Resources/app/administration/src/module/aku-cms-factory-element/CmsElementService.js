//const Twig = require('twig');

let getNameDuplicates = function(fields, prefix) {
    if (undefined === prefix) {
        prefix = '';
    }
    let names = [];
    let duplicates = [];
    fields.forEach(function(field){
        if (-1 < names.indexOf(field.name)) {
            duplicates.push(prefix + field.name)
        } else {
            names.push(field.name);
        }
        if (0 < field.children.length) {
            let prefix = field.name + '[i].';
            let sub_duplicates = getNameDuplicates(field.children, prefix);
            sub_duplicates.forEach(function(dup){
                duplicates.push(dup);
            })
        }

    })
    return duplicates;
}

export default {
    /**
     * 
     * @param {CmsFactoryElement} element 
     * @param {Vue} vm 
     */
    validate: function(element, fields, vm) {
        let errors = {}
        
        if (null === element.name || '' === String(element.name).trim()) {
            errors['name'] = {
                detail: vm.$tc('aku-cms-factory-element.edit.inputRequired')
            }
        }
        /* siehe Support Ticket ID #191417: Elemente ohne Felder zulassen
        if (0 == fields.length) {
            errors['fields'] = {
                detail: vm.$tc('aku-cms-factory-element.edit.fieldsRequired')
            }
        } else 
        */
        if (null === element.twig ||'' === String(element.twig).trim()) {
            errors['twig'] = {
                detail: vm.$tc('aku-cms-factory-element.edit.templateRequired')
            }
        } 
                        
        if (0 < fields.length) {
            let duplicates = getNameDuplicates(fields);
            if (0 < duplicates.length) {
                errors['fields'] = {
                    detail: vm.$tc('aku-cms-factory-element.edit.fieldNameDuplicates'),
                    duplicates: duplicates
                }
            }
        }
        

        return errors;
    }


}