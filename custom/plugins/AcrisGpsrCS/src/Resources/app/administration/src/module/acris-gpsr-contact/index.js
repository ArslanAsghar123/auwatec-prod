import './page/acris-gpsr-contact-create';
import './page/acris-gpsr-contact-detail';
import './page/acris-gpsr-contact-list';

import deDE from "./snippet/de-DE";
import enGB from "./snippet/en-GB";

const { Module } = Shopware;

Module.register('acris-gpsr-contact', {
    type: 'plugin',
    name: 'AcrisGpsrContact',
    title: 'acris-gpsr-contact.general.mainMenuItemGeneral',
    description: 'acris-gpsr-contact.general.description',
    color: '#a6c836',
    icon: 'regular-user',
    favicon: 'icon-module-settings.png',

    snippets: {
        'de-DE': deDE,
        'en-GB': enGB,
    },

    routes: {
        index: {
            component: 'acris-gpsr-contact-list',
            path: 'index',
            meta: {
                parentPath: 'acris.gpsr.menu.index'
            }
        },
        detail: {
            component: 'acris-gpsr-contact-detail',
            path: 'detail/:id',
            meta: {
                parentPath: 'acris.gpsr.contact.index'
            }
        },
        create: {
            component: 'acris-gpsr-contact-create',
            path: 'create',
            meta: {
                parentPath: 'acris.gpsr.contact.index'
            }
        }
    }
});
