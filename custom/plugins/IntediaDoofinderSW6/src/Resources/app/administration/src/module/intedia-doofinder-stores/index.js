import './page/intedia-doofinder-stores';

import deDE from "./snippet/de-DE";
import enGB from "./snippet/en-GB";

const { Module } = Shopware;

Module.register('intedia-doofinder-stores', {
    type: 'plugin',
    name: 'intedia-doofinder-stores',
    title: 'intedia-doofinder-stores.general.mainMenuItemGeneral',
    description: 'intedia-doofinder-stores.general.descriptionTextModule',
    version: '1.0.0',
    targetVersion: '1.0.0',
    color: '#33268c',
    icon: 'solid-search',

    snippets: {
        'de-DE': deDE,
        'en-GB': enGB
    },

    routes: {
        index: {
            component: 'intedia-doofinder-stores',
            path: 'stores',
            icon: 'solid-search',
            meta: {
                parentPath: 'intedia.doofinder.index.index'
            }
        }
    }
});
