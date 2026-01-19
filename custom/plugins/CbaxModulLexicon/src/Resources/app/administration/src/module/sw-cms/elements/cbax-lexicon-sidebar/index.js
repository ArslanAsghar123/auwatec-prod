import './component';
import './config';
import './preview';


Shopware.Service('cmsService').registerCmsElement({
    name: 'cbax-lexicon-sidebar',
    label: 'elements.cbax-lexicon-sidebar.general.label',
    component: 'sw-cms-el-cbax-lexicon-sidebar',
    configComponent: 'sw-cms-el-config-cbax-lexicon-sidebar',
    previewComponent: 'sw-cms-el-preview-cbax-lexicon-sidebar',
    disabledConfigInfoTextKey: 'elements.no-settings'
});
