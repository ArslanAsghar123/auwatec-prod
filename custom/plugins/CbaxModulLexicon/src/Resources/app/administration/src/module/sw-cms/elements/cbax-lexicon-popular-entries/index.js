import './component';
import './config';
import './preview';

Shopware.Service('cmsService').registerCmsElement({
    name: 'cbax-lexicon-popular-entries',
    label: 'elements.cbax-lexicon-popular-entries.general.label',
    component: 'sw-cms-el-cbax-lexicon-popular-entries',
    configComponent: 'sw-cms-el-config-cbax-lexicon-popular-entries',
    previewComponent: 'sw-cms-el-preview-cbax-lexicon-popular-entries',
    defaultConfig: {
        headline: {
            source: 'static',
            value: 'Die meistgelesenen Einträge'
        },
        template: {
            source: 'static',
            value: 'listing_3col'
        },
        entryNumber: {
            source: 'static',
            value: 3
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
