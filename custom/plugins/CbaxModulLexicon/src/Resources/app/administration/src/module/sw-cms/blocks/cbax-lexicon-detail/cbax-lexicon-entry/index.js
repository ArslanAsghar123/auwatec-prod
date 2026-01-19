import './component';
import './preview';

Shopware.Service('cmsService').registerCmsBlock({
    name: 'cbax-lexicon-entry',
    label: 'blocks.cbax-lexicon.cbax-lexicon-entry.general.label',
    category: 'cbax-lexicon-detail',
    component: 'sw-cms-block-cbax-lexicon-entry',
    previewComponent: 'sw-cms-preview-cbax-lexicon-entry',
    defaultConfig: {
        marginBottom: '20px',
        marginTop: '20px',
        marginLeft: '20px',
        marginRight: '20px',
        sizingMode: 'boxed'
    },
    slots: {
        content: 'cbax-lexicon-entry'
    }
});
