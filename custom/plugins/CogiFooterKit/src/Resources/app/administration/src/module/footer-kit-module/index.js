import './page/footer-kit-module-overview';
import './page/footer-kit-module-list';
import './page/footer-kit-module-create';

import deDE from './snippet/de-DE.json';
import enGB from './snippet/en-GB.json';

Shopware.Module.register('footer-kit-module', {
    type: 'plugin',
    name: 'FooterKit',
    color: '#66a1ff',
    title: 'footer-kit.general.footerKitSettingsLabel',
    description: 'Manage footer here.',

    snippets: {
        'de-DE': deDE,
        'en-GB': enGB
    },

    routes: {
        list: {
            component: 'footer-kit-module-list',
            path: 'list'
        },
        overview: {
            component: 'footer-kit-module-overview',
            path: 'overview:id'
        },
        create: {
            component: 'footer-kit-module-create',
            path: 'create'
        }
    },

    navigation: [{
        label: "footer-kit.general.footerKitSettingsLabel",
        color: "#ff3d58",
        path: "footer.kit.module.list",
        position: 100,
        parent: "sw-content"
    }]

});