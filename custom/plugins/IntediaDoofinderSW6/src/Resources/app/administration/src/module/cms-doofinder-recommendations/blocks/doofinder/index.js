import './component';
import './preview';

Shopware.Service('cmsService').registerCmsBlock({
    name: 'doofinder-recommendation',
    category: 'doofinder-elements',
    label: 'sw-cms.blocks.doofinder.label',
    component: 'sw-cms-block-doofinder-recommendation',
    previewComponent: 'sw-cms-preview-doofinder-recommendation',
    defaultConfig: {
        marginBottom: null,
        marginTop: null,
        marginLeft: null,
        marginRight: null,
        sizingMode: 'full_width'
    },
    slots: {
        doofinderRecommendation: {
            type: 'intedia-doofinder-recommendation-element',
            default: {
                config: {
                    displayMode: { source: 'static', value: 'cover' },
                },
            },
        },
    },
});