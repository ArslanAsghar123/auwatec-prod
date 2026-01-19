import './module/acris-gpsr-menu';
import './module/acris-gpsr-note';
import './module/acris-gpsr-contact';
import './module/acris-gpsr-manufacturer';

import './extension/sw-manufacturer';
import './extension/sw-product';
import './extension/sw-product/page/sw-product-detail'
    ;
import './component/acris-gpsr-product-download-form';
import './component/media/acris-gpsr-document-upload';
import './component/base/acris-gpsr-product-download';
import './component/acris-gpsr-file-download-form';
import './component/media/acris-document-upload-gpsr';
import './component/acris-gpsr-edit-download-modal';
import './component/acris-gpsr-info-text';

import './module/sw-import-export/component/sw-import-export-edit-profile-general';
import './module/sw-import-export/component/sw-import-export-entity-path-select';

import './view/sw-product-detail-gpsr';
import './component/acris-gpsr-product-detail';

Shopware.Module.register('sw-new-tab-custom', {

    routeMiddleware(next, currentRoute) {
        const customRouteName = 'sw.product.detail.gpsr';

        if (
            currentRoute.name === 'sw.product.detail') {
            currentRoute.children.push({
                name: customRouteName,
                path: '/sw/product/detail/:id/custom',
                component: 'sw-product-detail-gpsr',
                meta: {
                    parentPath: 'sw.product.index'
                }
            });
        }
        next(currentRoute);
    }
});

