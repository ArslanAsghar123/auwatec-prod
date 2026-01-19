import './component';
import './preview';

Shopware.Service('cmsService').registerCmsBlock({
    name: 'last-seen-product-slider',
    label: 'sw-cms.blocks.commerce.lastSeenProductSlider.label',
    category: 'commerce',
    component: 'sw-cms-block-last-seen-product-slider',
    previewComponent: 'sw-cms-preview-last-seen-product-slider',
    defaultConfig: {
        marginBottom: '20px',
        marginTop: '20px',
        marginLeft: '20px',
        marginRight: '20px',
        sizingMode: 'boxed'
    },
    slots: {
        productSlider: 'last-seen-product-slider'
    }
});
