import Plugin from 'src/plugin-system/plugin.class';

export default class ChooseBusinessAccount extends Plugin {
    static options = {
    };

    init() {
        this._setSelects(this.el);
        this._shippingAddressBugfix();
        this._resortFormFields();
        this.addressEditors = document.querySelectorAll('[data-address-editor]');
        this.addressEditors.forEach((addressEditor)=>{
            let addressEditorPlugin = window.PluginManager.getPluginInstanceFromElement(addressEditor, 'AddressEditor');
            addressEditorPlugin.$emitter.subscribe('onOpen', this._onAddressEditorOpen.bind(this));
        });
    }

    _onAddressEditorOpen(evt){
        let modal = evt.detail.pseudoModal.getModal();
        this._setSelects(modal);
    }

    _setSelects(el){
        let selects = el.querySelectorAll('[name="address[accountType]"], [name="shippingAddress[accountType]"], #accountType');
        selects.forEach((select)=>{
            let event = new Event('change', {
                bubbles: true,
                cancelable: true,
            });
            select.value = "business";
            select.dispatchEvent(event);
        });
    }

    _shippingAddressBugfix(){
        let shippingAddresscompany = document.getElementById('shippingAddresscompany');
        let differentShippingAddress = document.getElementById('differentShippingAddress');
        //Das Firmenfeld bei der abweichenden Lieferadresse wird durch den change-trigger required gesetzt
        //Dies macht das rueckgangig falls abweichende Lieferadresse nicht angezeigt wird
        if(shippingAddresscompany && differentShippingAddress){
            if(!differentShippingAddress.checked){
                shippingAddresscompany.setAttribute('disabled', 'disabled');
                shippingAddresscompany.classList.add('js-field-toggle-was-required');
                shippingAddresscompany.classList.remove('js-field-toggle-was-disabled');
                shippingAddresscompany.removeAttribute('required');
            }
        }
    }

    _resortFormFields(){
        let personalMail = document.getElementById('personalMail');
        let personalFirstName = document.getElementById('personalFirstName');
        if(personalMail && personalFirstName){
            let personalMailCol = personalMail.parentNode;
            let personalFirstNameRow = personalFirstName.parentNode.parentNode;
            personalFirstNameRow.appendChild(personalMailCol);
        }

        let billingAddresscompany = document.getElementById('billingAddresscompany');
        let billingAddressdepartment = document.getElementById('billingAddressdepartment');
        if(billingAddresscompany && billingAddressdepartment){
            let billingAddresscompanyCol = billingAddresscompany.parentNode;
            let billingAddressdepartmentRow = billingAddressdepartment.parentNode.parentNode;
            billingAddressdepartmentRow.prepend(billingAddresscompanyCol);
        }

        let shippingAddresscompany = document.getElementById('shippingAddresscompany');
        let shippingAddresspersonalFirstName = document.getElementById('shippingAddresspersonalFirstName');
        if(billingAddresscompany && billingAddressdepartment){
            let shippingAddresscompanyCol = shippingAddresscompany.parentNode;
            let shippingAddresspersonalRow = shippingAddresspersonalFirstName.parentNode.parentNode;
            shippingAddresspersonalRow.prepend(shippingAddresscompanyCol);
            shippingAddresspersonalRow.classList.add('register-shipping-name-row')
        }

        let shippingAddressdepartment = document.getElementById('shippingAddressdepartment');
        if(shippingAddressdepartment){
            let shippingAddressdepartmentRow = shippingAddressdepartment.parentNode.parentNode;
            shippingAddressdepartmentRow.classList.add('d-none')
        }

    }
}