import './page/acris-gpsr-note-create';
import './page/acris-gpsr-note-detail';
import './page/acris-gpsr-note-list';

import deDE from "./snippet/de-DE";
import enGB from "./snippet/en-GB";

const { Module } = Shopware;

Module.register('acris-gpsr-note', {
    type: 'plugin',
    name: 'AcrisGpsrNote',
    title: 'acris-gpsr-note.general.mainMenuItemGeneral',
    description: 'acris-gpsr-note.general.description',
    color: '#a6c836',
    icon: 'regular-exclamation-triangle',
    favicon: 'icon-module-settings.png',

    snippets: {
        'de-DE': deDE,
        'en-GB': enGB,
    },

    routes: {
        index: {
            component: 'acris-gpsr-note-list',
            path: 'index',
            meta: {
                parentPath: 'acris.gpsr.menu.index'
            }
        },
        detail: {
            component: 'acris-gpsr-note-detail',
            path: 'detail/:id',
            meta: {
                parentPath: 'acris.gpsr.note.index'
            }
        },
        create: {
            component: 'acris-gpsr-note-create',
            path: 'create',
            meta: {
                parentPath: 'acris.gpsr.note.index'
            }
        }
    }
});
