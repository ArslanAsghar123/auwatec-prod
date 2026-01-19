import './component';
import './config';
import './preview';


Shopware.Service('cmsService').registerCmsElement({
    name: 'cbax-lexicon-navigation',
    label: 'elements.cbax-lexicon-navigation.general.label',
    component: 'sw-cms-el-cbax-lexicon-navigation',
    configComponent: 'sw-cms-el-config-cbax-lexicon-navigation',
    previewComponent: 'sw-cms-el-preview-cbax-lexicon-navigation',
    disabledConfigInfoTextKey: 'elements.no-settings'
});
