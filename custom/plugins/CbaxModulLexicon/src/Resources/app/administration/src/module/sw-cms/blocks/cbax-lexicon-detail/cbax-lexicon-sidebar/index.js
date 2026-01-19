import './component';
import './preview';

Shopware.Service('cmsService').registerCmsBlock({
    name: 'cbax-lexicon-sidebar',
    label: 'blocks.cbax-lexicon.cbax-lexicon-sidebar.general.label',
    category: 'cbax-lexicon-detail',
    component: 'sw-cms-block-cbax-lexicon-sidebar',
    previewComponent: 'sw-cms-preview-cbax-lexicon-sidebar',
    defaultConfig: {
        marginBottom: '20px',
        marginTop: '20px',
        marginLeft: '20px',
        marginRight: '20px',
        sizingMode: 'boxed'
    },
    slots: {
        content: 'cbax-lexicon-sidebar'
    }
});
