import './component';
import './config';
import './preview';

Shopware.Service('cmsService').registerCmsElement({
    name: 'intedia-doofinder-recommendation-element',
    label: 'sw-cms.elements.intedia-doofinder-element.label',

    component: 'doofinder-element-component',
    configComponent: 'doofinder-element-config',
    previewComponent: 'doofinder-element-preview',

    defaultConfig: {
        totalProducts: {
            source: 'static',
            value: '10'
        },
        title: {
            source: 'static',
            value: ''
        }

    }
});
