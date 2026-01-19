import './component';
import './config';
import './preview';


Shopware.Service('cmsService').registerCmsElement({
    name: 'cbax-lexicon-content',
    label: 'elements.cbax-lexicon-content.general.label',
    component: 'sw-cms-el-cbax-lexicon-content',
    configComponent: 'sw-cms-el-config-cbax-lexicon-content',
    previewComponent: 'sw-cms-el-preview-cbax-lexicon-content',
    disabledConfigInfoTextKey: 'elements.no-settings'
});
