import './fieldgroup';
import './repeater';
import './config';
import './preview';
import './media-preview';
import './category-preview';
import './product-preview';
import './manufacturer-preview';
import './custom-entity-preview';
import './product-stream-preview';
import './aku-cms-factory-text-field';
import './aku-cms-factory-textarea-field';
import './gallery-field';
import './component';

Shopware.Service('cmsService').registerCmsElement({
    name: 'aku-cms-factory',
    label: 'sw-cms.element.akuCmsFactory.labelLabel',
    component: 'sw-cms-el-aku-cms-factory',
    configComponent: 'sw-cms-el-config-aku-cms-factory',
    previewComponent: 'sw-cms-el-preview-aku-cms-factory',
    defaultConfig: {
        field_values: {
            source: 'static',
            value: ""
        },
        cms_factory_element_id: {
            source: 'static',
            value: ''
        }
    }
});