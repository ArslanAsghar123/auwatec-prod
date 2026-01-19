const profileTypes = {
    IMPORT: 'import',
    EXPORT: 'export',
    IMPORT_EXPORT: 'import-export',
};

/**
 * @private
 */
Shopware.Component.override('sw-import-export-edit-profile-general', {

    created() {
        this.supportedEntities = this.supportedEntities.push(this.discountGroupEntity);
    },

    data() {
        return {
            discountGroupEntity:
                {
                    value: 'acris_discount_group',
                    label: this.$tc('acris-discount-group.import-export-profile.discountGroupLabel'),
                    type: profileTypes.IMPORT_EXPORT
                }
        };
    }
});
