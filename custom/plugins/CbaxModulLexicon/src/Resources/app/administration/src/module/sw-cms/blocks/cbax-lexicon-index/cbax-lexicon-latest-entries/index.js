import './component';
import './preview';

Shopware.Service('cmsService').registerCmsBlock({
    name: 'cbax-lexicon-latest-entries',
    label: 'blocks.cbax-lexicon.cbax-lexicon-latest-entries.general.label',
    category: 'cbax-lexicon-index',
    component: 'sw-cms-block-cbax-lexicon-latest-entries',
    previewComponent: 'sw-cms-preview-cbax-lexicon-latest-entries',
    defaultConfig: {
        marginBottom: '20px',
        marginTop: '20px',
        marginLeft: '20px',
        marginRight: '20px',
        sizingMode: 'boxed'
    },
    slots: {
        content: 'cbax-lexicon-latest-entries'
    }
});
