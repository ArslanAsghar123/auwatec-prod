//see: https://docs.shopware.com/en/shopware-platform-dev-en/developer-guide/administration/acl
if (Shopware.Service('privileges') !== undefined) {
    Shopware.Service('privileges').addPrivilegeMappingEntry({
        category: 'permissions',
        parent: null,
        key: 'swpa_backup',
        roles: {
            viewer: {
                privileges: [
                    'swpa_backup:viewer',
                    'swpa_backup:read'
                ],
                dependencies: []
            },
            editor: {
                privileges: [
                    'swpa_backup:editor',
                    'system_config:update',
                    'system_config:create',
                    'system_config:delete',
                ],
                dependencies: [
                    'swpa_backup.viewer'
                ]
            },
            creator: {
                privileges: [
                    'swpa_backup:creator'
                ],
                dependencies: [
                    'swpa_backup.editor'
                ]
            },
            deleter: {
                privileges: [
                    'swpa_backup:deleter'
                ],
                dependencies: [
                    'swpa_backup.creator'
                ]
            }
        }
    });

}
