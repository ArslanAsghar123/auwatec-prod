import './component';
import './config';
import './preview';

Shopware.Service('cmsService').registerCmsElement({
    name: 'last-seen-product-slider',
    label: 'sw-cms.elements.lastSeenProductSlider.label',
    component: 'sw-cms-el-last-seen-product-slider',
    configComponent: 'sw-cms-el-config-last-seen-product-slider',
    previewComponent: 'sw-cms-el-preview-last-seen-product-slider',
    defaultConfig: {
        title: {
            source: 'static',
            value: ''
        },
        text: {
            source: 'static',
            value: ''
        },
        ajaxLoad: {
            source: 'static',
            value: true
        },
        displayMode: {
            source: 'static',
            value: 'standard',
        },
        boxLayout: {
            source: 'static',
            value: 'standard',
        },
        navigation: {
            source: 'static',
            value: true,
        },
        rotate: {
            source: 'static',
            value: false,
        },
        border: {
            source: 'static',
            value: false,
        },
        verticalAlign: {
            source: 'static',
            value: null,
        },
        elMinWidth: {
            source: 'static',
            value: '300px',
        },
    }
});
