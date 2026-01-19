import template from './sw-manufacturer-detail.html.twig';

const { Criteria } = Shopware.Data;

Shopware.Component.override('sw-manufacturer-detail', {
    template,

    data() {
        return {
            excludedCustomFieldSets: [
                'acris_gpsr_manufacturer',
                'acris_gpsr_contact'
            ],
            acris_gpsr_manufacturer_shopware_link: 'standard',
            acris_gpsr_manufacturer_name: null,
            acris_gpsr_manufacturer_street: null,
            acris_gpsr_manufacturer_house_number: null,
            acris_gpsr_manufacturer_zipcode: null,
            acris_gpsr_manufacturer_city: null,
            acris_gpsr_manufacturer_country: null,
            acris_gpsr_manufacturer_phone_number: null,
            acris_gpsr_manufacturer_address: null,
            acris_gpsr_contact_name: null,
            acris_gpsr_contact_street: null,
            acris_gpsr_contact_house_number: null,
            acris_gpsr_contact_zipcode: null,
            acris_gpsr_contact_city: null,
            acris_gpsr_contact_country: null,
            acris_gpsr_contact_phone_number: null,
            acris_gpsr_contact_address: null,
        };
    },

    computed: {
        customFieldSetCriteria() {
            const criteria = this.$super('customFieldSetCriteria');
            criteria.addFilter(Criteria.not('and', [Criteria.equalsAny('name', this.excludedCustomFieldSets)]));

            return criteria;
        },

        manufacturerRepository() {
            return this.repositoryFactory.create('product_manufacturer');
        },
    },

    methods: {
        async loadEntityData(){
            await this.$super('loadEntityData');

            this.refreshCustomFields();
        },

        refreshCustomFields() {
            if (this.manufacturer && this.manufacturer.customFields != null) {
                this.acris_gpsr_manufacturer_shopware_link = this.manufacturer.customFields.acris_gpsr_manufacturer_shopware_link;
                this.acris_gpsr_manufacturer_name = this.manufacturer.customFields.acris_gpsr_manufacturer_name;
                this.acris_gpsr_manufacturer_street = this.manufacturer.customFields.acris_gpsr_manufacturer_street;
                this.acris_gpsr_manufacturer_house_number = this.manufacturer.customFields.acris_gpsr_manufacturer_house_number;
                this.acris_gpsr_manufacturer_zipcode = this.manufacturer.customFields.acris_gpsr_manufacturer_zipcode;
                this.acris_gpsr_manufacturer_city = this.manufacturer.customFields.acris_gpsr_manufacturer_city;
                this.acris_gpsr_manufacturer_country = this.manufacturer.customFields.acris_gpsr_manufacturer_country;
                this.acris_gpsr_manufacturer_phone_number = this.manufacturer.customFields.acris_gpsr_manufacturer_phone_number;
                this.acris_gpsr_manufacturer_address = this.manufacturer.customFields.acris_gpsr_manufacturer_address;

                this.acris_gpsr_contact_name = this.manufacturer.customFields.acris_gpsr_contact_name;
                this.acris_gpsr_contact_street = this.manufacturer.customFields.acris_gpsr_contact_street;
                this.acris_gpsr_contact_house_number = this.manufacturer.customFields.acris_gpsr_contact_house_number;
                this.acris_gpsr_contact_zipcode = this.manufacturer.customFields.acris_gpsr_contact_zipcode;
                this.acris_gpsr_contact_city = this.manufacturer.customFields.acris_gpsr_contact_city;
                this.acris_gpsr_contact_country = this.manufacturer.customFields.acris_gpsr_contact_country;
                this.acris_gpsr_contact_phone_number = this.manufacturer.customFields.acris_gpsr_contact_phone_number;
                this.acris_gpsr_contact_address = this.manufacturer.customFields.acris_gpsr_contact_address;

            } else {
                this.acris_gpsr_manufacturer_shopware_link = 'standard';
                this.acris_gpsr_manufacturer_name = null;
                this.acris_gpsr_manufacturer_street = null;
                this.acris_gpsr_contact_house_number = null;
                this.acris_gpsr_manufacturer_zipcode = null;
                this.acris_gpsr_manufacturer_city = null;
                this.acris_gpsr_manufacturer_country = null;
                this.acris_gpsr_manufacturer_phone_number = null;
                this.acris_gpsr_manufacturer_address = null;

                this.acris_gpsr_contact_name = null;
                this.acris_gpsr_contact_street = null;
                this.acris_gpsr_contact_house_number = null;
                this.acris_gpsr_contact_zipcode = null;
                this.acris_gpsr_contact_city = null;
                this.acris_gpsr_contact_country = null;
                this.acris_gpsr_contact_phone_number = null;
                this.acris_gpsr_contact_address = null;
            }
        },

        onChangeManufacturer() {
            this.createDefaultCustomFieldsIfNotExists();
            this.manufacturer.customFields = {
                acris_gpsr_manufacturer_shopware_link: this.acris_gpsr_manufacturer_shopware_link,
                acris_gpsr_manufacturer_name: this.acris_gpsr_manufacturer_name,
                acris_gpsr_manufacturer_street: this.acris_gpsr_manufacturer_street,
                acris_gpsr_manufacturer_house_number: this.acris_gpsr_manufacturer_house_number,
                acris_gpsr_manufacturer_zipcode: this.acris_gpsr_manufacturer_zipcode,
                acris_gpsr_manufacturer_city: this.acris_gpsr_manufacturer_city,
                acris_gpsr_manufacturer_country: this.acris_gpsr_manufacturer_country,
                acris_gpsr_manufacturer_phone_number: this.acris_gpsr_manufacturer_phone_number,
                acris_gpsr_manufacturer_address: this.acris_gpsr_manufacturer_address,

                acris_gpsr_contact_name: this.acris_gpsr_contact_name,
                acris_gpsr_contact_street: this.acris_gpsr_contact_street,
                acris_gpsr_contact_house_number: this.acris_gpsr_contact_house_number,
                acris_gpsr_contact_zipcode: this.acris_gpsr_contact_zipcode,
                acris_gpsr_contact_city: this.acris_gpsr_contact_city,
                acris_gpsr_contact_country: this.acris_gpsr_contact_country,
                acris_gpsr_contact_phone_number: this.acris_gpsr_contact_phone_number,
                acris_gpsr_contact_address: this.acris_gpsr_contact_address
            };
        },

        createDefaultCustomFieldsIfNotExists() {
            if (this.manufacturer && this.manufacturer.customFields == null) {
                this.manufacturer.customFields = {
                    acris_gpsr_manufacturer_shopware_link: 'standard',
                    acris_gpsr_manufacturer_name: null,
                    acris_gpsr_manufacturer_street: null,
                    acris_gpsr_manufacturer_house_number: null,
                    acris_gpsr_manufacturer_zipcode: null,
                    acris_gpsr_manufacturer_city: null,
                    acris_gpsr_manufacturer_country: null,
                    acris_gpsr_manufacturer_phone_number: null,
                    acris_gpsr_manufacturer_address: null,
                    acris_gpsr_contact_name: null,
                    acris_gpsr_contact_street: null,
                    acris_gpsr_contact_house_number: null,
                    acris_gpsr_contact_zipcode: null,
                    acris_gpsr_contact_city: null,
                    acris_gpsr_contact_country: null,
                    acris_gpsr_contact_phone_number: null,
                    acris_gpsr_contact_address: null,
                };
            }
        },
    }
});