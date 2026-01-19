import './component';
import './config';
import './preview';


Shopware.Service('cmsService').registerCmsElement({
    name: 'cbax-lexicon-products',
    label: 'elements.cbax-lexicon-products.general.label',
    component: 'sw-cms-el-cbax-lexicon-products',
    configComponent: 'sw-cms-el-config-cbax-lexicon-products',
    previewComponent: 'sw-cms-el-preview-cbax-lexicon-products',
    disabledConfigInfoTextKey: 'elements.cbax-lexicon-products.no-settings'
});
