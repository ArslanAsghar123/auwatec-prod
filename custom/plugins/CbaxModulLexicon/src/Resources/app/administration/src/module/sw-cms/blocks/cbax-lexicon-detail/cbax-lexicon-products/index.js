import './component';
import './preview';

Shopware.Service('cmsService').registerCmsBlock({
    name: 'cbax-lexicon-products',
    label: 'blocks.cbax-lexicon.cbax-lexicon-products.general.label',
    category: 'cbax-lexicon-detail',
    component: 'sw-cms-block-cbax-lexicon-products',
    previewComponent: 'sw-cms-preview-cbax-lexicon-products',
    defaultConfig: {
        marginBottom: '20px',
        marginTop: '20px',
        marginLeft: '20px',
        marginRight: '20px',
        sizingMode: 'boxed'
    },
    slots: {
        content: 'cbax-lexicon-products'
    }
});
