import './page/acris-gpsr-index';
import './acris-settings-item.scss';

import deDE from "./snippet/de-DE";
import enGB from "./snippet/en-GB";

const { Module } = Shopware;

Module.register('acris-gpsr-menu', {
    type: 'plugin',
    name: 'AcrisGpsr',
    title: 'acris-gpsr-menu.general.mainMenuItemGeneral',
    description: 'acris-gpsr-menu.general.descriptionTextModule',
    version: '1.0.0',
    targetVersion: '1.0.0',
    color: '#a6c836',
    icon: 'regular-shield',

    snippets: {
        'de-DE': deDE,
        'en-GB': enGB
    },

    routes: {
        index: {
            component: 'acris-gpsr-index',
            path: 'index',
            icon: 'regular-shield',
            meta: {
                parentPath: 'sw.settings.index.plugins'
            }
        }
    },

    settingsItem: [
        {
            name:   'acris-gpsr-menu-index',
            to:     'acris.gpsr.menu.index',
            label:  'acris-gpsr-menu.general.mainMenuItemGeneral',
            group:  'plugins',
            icon:   'regular-shield'
        }
    ]
});
