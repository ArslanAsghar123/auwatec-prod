import './preview';
import './component';

Shopware.Service('cmsService').registerCmsBlock({
    name: 'aku-cms-factory',
    label: 'sw-cms.blocks.akuCmsFactory.labelLabel',
    category: 'text',
    component: 'sw-cms-block-aku-cms-factory',
    previewComponent: 'sw-cms-preview-aku-cms-factory',
    defaultConfig: {
        marginBottom: '20px',
        marginTop: '20px',
        marginLeft: '20px',
        marginRight: '20px',
        sizingMode: 'boxed'
    },
    slots: {
        content: 'aku-cms-factory'
    }
});