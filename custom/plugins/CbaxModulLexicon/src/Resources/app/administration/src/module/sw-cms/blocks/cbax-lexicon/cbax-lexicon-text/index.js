import './component';
import './preview';

Shopware.Service('cmsService').registerCmsBlock({
    name: 'cbax-lexicon-text',
    label: 'blocks.text.cbax-lexicon-text.general.label',
    category: 'cbax-lexicon',
    component: 'sw-cms-block-cbax-lexicon-text',
    previewComponent: 'sw-cms-preview-cbax-lexicon-text',
    defaultConfig: {
        marginBottom: '20px',
        marginTop: '20px',
        marginLeft: '20px',
        marginRight: '20px',
        sizingMode: 'boxed'
    },
    slots: {
        content: 'cbax-lexicon-text'
    }
});
