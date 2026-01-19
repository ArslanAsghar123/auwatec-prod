import './page/acris-discount-group-menu';

const { Module } = Shopware;

Module.register('acris-discount-group-menu', {
    type: 'plugin',
    name: 'AcrisDiscountGroup',
    title: 'acris-discount-group.general.mainMenuItemGeneral',
    description: 'acris-discount-group.general.descriptionTextModule',
    version: '1.0.0',
    targetVersion: '1.0.0',
    color: '#a6c836',
    icon: 'regular-cog',

    routes: {
        index: {
            component: 'acris-discount-group-menu',
            path: 'index',
            icon: 'regular-cog',
            meta: {
                parentPath: 'sw.settings.index.plugins'
            }
        }
    },

    settingsItem: [
        {
            name:   'acris-discount-group-menu',
            to:     'acris.discount.group.menu.index',
            label:  'acris-discount-group.general.mainMenuItemGeneral',
            group:  'plugins',
            icon:   'regular-cog'
        }
    ]
});
