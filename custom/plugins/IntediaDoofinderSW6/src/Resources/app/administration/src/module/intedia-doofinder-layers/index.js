import './page/intedia-doofinder-layers';

import deDE from "./snippet/de-DE";
import enGB from "./snippet/en-GB";

const { Module } = Shopware;

Module.register('intedia-doofinder-layers', {
    type: 'plugin',
    name: 'intedia-doofinder-layers',
    title: 'intedia-doofinder-layers.general.mainMenuItemGeneral',
    description: 'intedia-doofinder-layers.general.descriptionTextModule',
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
            component: 'intedia-doofinder-layers',
            path: 'layers',
            icon: 'solid-search',
            meta: {
                parentPath: 'intedia.doofinder.index.index'
            }
        }
    }
});
