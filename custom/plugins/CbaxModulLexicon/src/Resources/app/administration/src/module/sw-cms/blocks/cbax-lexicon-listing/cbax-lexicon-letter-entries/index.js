import './component';
import './preview';

Shopware.Service('cmsService').registerCmsBlock({
    name: 'cbax-lexicon-letter-entries',
    label: 'blocks.cbax-lexicon.cbax-lexicon-letter-entries.general.label',
    category: 'cbax-lexicon-listing',
    component: 'sw-cms-block-cbax-lexicon-letter-entries',
    previewComponent: 'sw-cms-preview-cbax-lexicon-letter-entries',
    defaultConfig: {
        marginBottom: '20px',
        marginTop: '20px',
        marginLeft: '20px',
        marginRight: '20px',
        sizingMode: 'boxed'
    },
    slots: {
        content: 'cbax-lexicon-letter-entries'
    }
});
