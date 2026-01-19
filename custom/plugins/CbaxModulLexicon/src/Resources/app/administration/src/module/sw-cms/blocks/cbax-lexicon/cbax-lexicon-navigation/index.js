import './component';
import './preview';

Shopware.Service('cmsService').registerCmsBlock({
    name: 'cbax-lexicon-navigation',
    label: 'blocks.cbax-lexicon.cbax-lexicon-navigation.general.label',
    category: 'cbax-lexicon',
    component: 'sw-cms-block-cbax-lexicon-navigation',
    previewComponent: 'sw-cms-preview-cbax-lexicon-navigation',
    defaultConfig: {
        marginBottom: '20px',
        marginTop: '20px',
        marginLeft: '20px',
        marginRight: '20px',
        sizingMode: 'boxed'
    },
    slots: {
        content: 'cbax-lexicon-navigation'
    }
});
