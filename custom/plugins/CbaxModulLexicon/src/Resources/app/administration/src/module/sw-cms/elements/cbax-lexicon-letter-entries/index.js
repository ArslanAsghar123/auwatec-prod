import './component';
import './config';
import './preview';


Shopware.Service('cmsService').registerCmsElement({
    name: 'cbax-lexicon-letter-entries',
    label: 'elements.cbax-lexicon-letter-entries.general.label',
    component: 'sw-cms-el-cbax-lexicon-letter-entries',
    configComponent: 'sw-cms-el-config-cbax-lexicon-letter-entries',
    previewComponent: 'sw-cms-el-preview-cbax-lexicon-letter-entries',
    defaultConfig: {
        template: {
            source: 'static',
            value: 'listing_3col'
        },
		buttonVariant: {
            source: 'static',
            value: 'btn-outline-secondary',
        },
		buttonSize: {
            source: 'static',
            value: 'btn-sm',
        }
    }
});
