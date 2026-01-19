import './page/acris-discount-group-list';
import './page/acris-discount-group-create';
import './page/acris-discount-group-detail';
import './acris-settings-item.scss';

import deDE from "./snippet/de-DE";
import enGB from "./snippet/en-GB";

const { Module } = Shopware;

Module.register('acris-discount-group', {
    type: 'plugin',
    name: 'AcrisDiscountGroup',
    title: 'acris-discount-group.general.mainMenuItemGeneral',
    description: 'acris-discount-group.general.descriptionTextModule',
    version: '1.0.0',
    targetVersion: '1.0.0',
    color: '#a6c836',
    icon: 'regular-users',
    favicon: 'icon-module-settings.png',

    snippets: {
        'de-DE': deDE,
        'en-GB': enGB
    },

    routes: {
        index: {
            component: 'acris-discount-group-list',
            path: 'index',
            meta: {
                parentPath: 'acris.discount.group.menu.index'
            }
        },
        detail: {
            component: 'acris-discount-group-detail',
            path: 'detail/:id',
            meta: {
                parentPath: 'acris.discount.group.index'
            }
        },
        create: {
            component: 'acris-discount-group-create',
            path: 'create',
            meta: {
                parentPath: 'acris.discount.group.index'
            }
        }
    }
});
