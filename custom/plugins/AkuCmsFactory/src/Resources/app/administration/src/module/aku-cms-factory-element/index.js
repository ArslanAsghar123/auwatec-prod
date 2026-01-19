import './page/aku-cms-factory-element-sample';
import './page/aku-cms-factory-element-list'
import './page/aku-cms-factory-element-import'
import './page/aku-cms-factory-element-create'
import './page/aku-cms-factory-element-edit'
import './page/aku-cms-factory-element-edit-field';
import './page/aku-cms-factory-element-multicheckbox';

Shopware.Module.register('aku-cms-factory-element', {
    type: 'plugin',
    name: 'CmsFactoryElement',
    title: 'aku-cms-factory-element.list.title',
    description: 'Cms Factory Elements',
    color: '#62ff80',
    routes: {
        overview: {
            component: 'aku-cms-factory-element-list',
            path: 'overview'
        },
        import: {
            component: 'aku-cms-factory-element-import',
            path: 'import'
        },
        detail: {
            component: 'aku-cms-factory-element-edit',
            path: 'detail/:id',
            meta: {
                parentPath: 'aku.cms.factory.element.overview'
            }
        },
        create: {
            component: 'aku-cms-factory-element-create',
            path: 'create',
            meta: {
                parentPath: 'aku.cms.factory.element.overview'
            }
        },
    },
    navigation: [{
        label: 'aku-cms-factory-element.list.title',
        color: '#62ff80',
        path: 'aku.cms.factory.element.overview',
        parent: 'sw-content'
    }]

});
