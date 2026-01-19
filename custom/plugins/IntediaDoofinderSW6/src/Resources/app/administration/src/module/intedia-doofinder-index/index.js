import './intedia-settings-item.scss'
import './page/intedia-doofinder-index';

import deDE from "./snippet/de-DE";
import enGB from "./snippet/en-GB";

const { Module } = Shopware;

Module.register('intedia-doofinder-index', {
    type: 'plugin',
    name: 'intedia-doofinder-index',
    title: 'intedia-doofinder-index.general.mainMenuItemGeneral',
    description: 'intedia-doofinder-layers.general.descriptionTextModule',
    version: '1.0.0',
    targetVersion: '1.0.0',
    color: '#33268c',
    icon: 'regular-cog',

    snippets: {
        'de-DE': deDE,
        'en-GB': enGB
    },

    routes: {
        index: {
            component: 'intedia-doofinder-index',
            path: 'index',
            icon: 'regular-cog',
            meta: {
                parentPath: 'sw.settings.index.plugins'
            }
        }
    },

    settingsItem: [
        {
            name:   'intedia-doofinder-index-index',
            to:     'intedia.doofinder.index.index',
            label:  'intedia-doofinder-index.general.mainMenuItemGeneral',
            group:  'plugins',
            icon:   'solid-search'
        }
    ]
});
