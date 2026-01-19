import './service/searchTypesServiceDecorator'

import './page/cbax-lexicon-list';
import './page/cbax-lexicon-detail';

import './component/cbax-lexicon-config-seo'
import './component/cbax-lexicon-customfields-multi-select';
import './component/cbax-lexicon-entity-visibility';
import './component/cbax-lexicon-many-to-many-assignment-card';

import './component/plugin-config/cbax-lexicon-config-alert';
import './component/plugin-config/cbax-lexicon-cmspage-single-select';
import './component/plugin-config/cbax-lexicon-import'

import deDE from './snippet/de-DE.json';
import enGB from './snippet/en-GB.json';

Shopware.Module.register('cbax-lexicon', {
    type: 'plugin',
    name: 'cbax-lexicon.general.name',
    title: 'cbax-lexicon.general.title',
    description: 'cbax-lexicon.general.description',
    color: '#ff68b4',
    icon: 'regular-flask',
    entity: 'cbax_lexicon_entry',
    snippets: {
        'de-DE': deDE,
        'en-GB': enGB
    },

	routes: {
        index: {
            components: {
                default: 'cbax-lexicon-list'
            },
            path: 'index'
        },
        create: {
            component: 'cbax-lexicon-detail',
            path: 'create',
            meta: {
                parentPath: 'cbax.lexicon.index'
            }
        },
        detail: {
            component: 'cbax-lexicon-detail',
            path: 'detail/:id',
            meta: {
                parentPath: 'cbax.lexicon.index'
            },
            props: {
                default(route) {
                    return {
                        lexiconEntryId: route.params.id
                    };
                }
            }
        }
    },

    navigation: [{
		id: 'cbax-lexicon',
        label: 'cbax-lexicon.general.navigationLabel',
        color: '#ff68b4',
		icon: 'regular-flask',
        path: 'cbax.lexicon.index',
		position: 100,
        parent: 'sw-content'
    }]
});
