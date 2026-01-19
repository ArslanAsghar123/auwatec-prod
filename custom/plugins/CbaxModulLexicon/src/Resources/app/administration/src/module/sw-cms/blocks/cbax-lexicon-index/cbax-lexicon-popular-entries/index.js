import './component';
import './preview';

Shopware.Service('cmsService').registerCmsBlock({
    name: 'cbax-lexicon-popular-entries',
    label: 'blocks.cbax-lexicon.cbax-lexicon-popular-entries.general.label',
    category: 'cbax-lexicon-index',
    component: 'sw-cms-block-cbax-lexicon-popular-entries',
    previewComponent: 'sw-cms-preview-cbax-lexicon-popular-entries',
    defaultConfig: {
        marginBottom: '20px',
        marginTop: '20px',
        marginLeft: '20px',
        marginRight: '20px',
        sizingMode: 'boxed'
    },
    slots: {
        content: 'cbax-lexicon-popular-entries'
    }
});
