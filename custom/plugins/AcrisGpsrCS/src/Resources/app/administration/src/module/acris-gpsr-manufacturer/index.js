import './page/acris-gpsr-manufacturer-create';
import './page/acris-gpsr-manufacturer-detail';
import './page/acris-gpsr-manufacturer-list';

import deDE from "./snippet/de-DE";
import enGB from "./snippet/en-GB";

const { Module } = Shopware;

Module.register('acris-gpsr-manufacturer', {
    type: 'plugin',
    name: 'AcrisGpsrManufacturer',
    title: 'acris-gpsr-manufacturer.general.mainMenuItemGeneral',
    description: 'acris-gpsr-manufacturer.general.description',
    color: '#a6c836',
    icon: 'regular-home',
    favicon: 'icon-module-settings.png',

    snippets: {
        'de-DE': deDE,
        'en-GB': enGB,
    },

    routes: {
        index: {
            component: 'acris-gpsr-manufacturer-list',
            path: 'index',
            meta: {
                parentPath: 'acris.gpsr.menu.index'
            }
        },
        detail: {
            component: 'acris-gpsr-manufacturer-detail',
            path: 'detail/:id',
            meta: {
                parentPath: 'acris.gpsr.manufacturer.index'
            }
        },
        create: {
            component: 'acris-gpsr-manufacturer-create',
            path: 'create',
            meta: {
                parentPath: 'acris.gpsr.manufacturer.index'
            }
        }
    }
});
