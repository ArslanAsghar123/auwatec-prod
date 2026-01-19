import './component';
import './config';
import './preview';

Shopware.Service('cmsService').registerCmsElement({
    name: 'cbax-lexicon-latest-entries',
    label: 'elements.cbax-lexicon-latest-entries.general.label',
    component: 'sw-cms-el-cbax-lexicon-latest-entries',
    configComponent: 'sw-cms-el-config-cbax-lexicon-latest-entries',
    previewComponent: 'sw-cms-el-preview-cbax-lexicon-latest-entries',
    defaultConfig: {
        headline: {
            source: 'static',
            value: 'Die neusten Einträge'
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
