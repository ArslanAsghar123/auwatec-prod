const { debounce, get, flow } = Shopware.Utils;

Shopware.Component.override('sw-import-export-entity-path-select', {
    methods: {
        getCustomFields(entityName) {

            let customFields = this.$super('getCustomFields',entityName)

            if(entityName === "product_manufacturer") {
                const gpsrCustomFields = {
                    "acris_gpsr_contact_city": {
                        "label": "acris_gpsr_contact_city",
                        "value": "acris_gpsr_contact_city"
                    },
                    "acris_gpsr_contact_name": {
                        "label": "acris_gpsr_contact_name",
                        "value": "acris_gpsr_contact_name"
                    },
                    "acris_gpsr_contact_street": {
                        "label": "acris_gpsr_contact_street",
                        "value": "acris_gpsr_contact_street"
                    },
                    "acris_gpsr_contact_address": {
                        "label": "acris_gpsr_contact_address",
                        "value": "acris_gpsr_contact_address"
                    },
                    "acris_gpsr_contact_country": {
                        "label": "acris_gpsr_contact_country",
                        "value": "acris_gpsr_contact_country"
                    },
                    "acris_gpsr_contact_zipcode": {
                        "label": "acris_gpsr_contact_zipcode",
                        "value": "acris_gpsr_contact_zipcode"
                    },
                    "acris_gpsr_manufacturer_shopware_link": {
                        "label": "acris_gpsr_manufacturer_shopware_link",
                        "value": "acris_gpsr_manufacturer_shopware_link"
                    },
                    "acris_gpsr_manufacturer_name": {
                        "label": "acris_gpsr_manufacturer_name",
                        "value": "acris_gpsr_manufacturer_name"
                    },
                    "acris_gpsr_contact_house_number": {
                        "label": "acris_gpsr_contact_house_number",
                        "value": "acris_gpsr_contact_house_number"
                    },
                    "acris_gpsr_contact_phone_number": {
                        "label": "acris_gpsr_contact_phone_number",
                        "value": "acris_gpsr_contact_phone_number"
                    },
                    "acris_gpsr_manufacturer_street": {
                        "label": "acris_gpsr_manufacturer_street",
                        "value": "acris_gpsr_manufacturer_street"
                    },
                    "acris_gpsr_manufacturer_house_number": {
                        "label": "acris_gpsr_manufacturer_house_number",
                        "value": "acris_gpsr_manufacturer_house_number"
                    },
                    "acris_gpsr_manufacturer_zipcode": {
                        "label": "acris_gpsr_manufacturer_zipcode",
                        "value": "acris_gpsr_manufacturer_zipcode"
                    },
                    "acris_gpsr_manufacturer_city": {
                        "label": "acris_gpsr_manufacturer_city",
                        "value": "acris_gpsr_manufacturer_city"
                    },
                    "acris_gpsr_manufacturer_country": {
                        "label": "acris_gpsr_manufacturer_country",
                        "value": "acris_gpsr_manufacturer_country"
                    },
                    "acris_gpsr_manufacturer_phone_number": {
                        "label": "acris_gpsr_manufacturer_phone_number",
                        "value": "acris_gpsr_manufacturer_phone_number"
                    },
                    "acris_gpsr_manufacturer_address": {
                        "label": "acris_gpsr_manufacturer_address",
                        "value": "acris_gpsr_manufacturer_address"
                    }
                };

                customFields = {
                    ...customFields,
                    ...gpsrCustomFields
                };
            }
            return customFields;
        }
    },
});