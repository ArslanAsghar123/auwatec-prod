import './component';
import './config';
import './preview';


Shopware.Service('cmsService').registerCmsElement({
    name: 'cbax-lexicon-entry',
    label: 'elements.cbax-lexicon-entry.general.label',
    component: 'sw-cms-el-cbax-lexicon-entry',
    configComponent: 'sw-cms-el-config-cbax-lexicon-entry',
    previewComponent: 'sw-cms-el-preview-cbax-lexicon-entry',
    disabledConfigInfoTextKey: 'elements.no-settings'
});
