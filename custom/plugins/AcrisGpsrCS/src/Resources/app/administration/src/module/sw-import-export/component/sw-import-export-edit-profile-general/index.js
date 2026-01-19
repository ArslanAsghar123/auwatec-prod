
const profileTypes = {
    IMPORT: 'import',
    EXPORT: 'export',
    IMPORT_EXPORT: 'import-export',
};
Shopware.Component.override('sw-import-export-edit-profile-general', {
    computed: {
        supportedEntities() {
            const supportedEntites = this.$super('supportedEntities')
            supportedEntites.push(
                {
                    value: 'product_manufacturer',
                    label: this.$tc('sw-product.basicForm.labelManufacturer')
                }
            );

            return supportedEntites;

        }
    },
    methods: {
        shouldDisableObjectType(item) {
            if(item.value === "product_manufacturer") {
                return false;
            }
            return this.$super('shouldDisableObjectType', item);
        },
    },
});