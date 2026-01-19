import './component';
import './preview';

Shopware.Service('cmsService').registerCmsBlock({
    name: 'cbax-lexicon-content',
    label: 'blocks.cbax-lexicon.cbax-lexicon-content.general.label',
    category: 'cbax-lexicon-content',
    component: 'sw-cms-block-cbax-lexicon-content',
    previewComponent: 'sw-cms-preview-cbax-lexicon-content',
    defaultConfig: {
        marginBottom: '20px',
        marginTop: '20px',
        marginLeft: '20px',
        marginRight: '20px',
        sizingMode: 'boxed'
    },
    slots: {
        content: 'cbax-lexicon-content'
    }
});
