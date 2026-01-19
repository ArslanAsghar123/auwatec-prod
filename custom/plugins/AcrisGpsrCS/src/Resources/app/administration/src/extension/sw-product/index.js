import deDE from "./snippet/de-DE";
import enGB from "./snippet/en-GB";

import './view/sw-product-detail-base';
import './page/sw-product-detail';

const { Module } = Shopware;

Module.register('acris-gpsr-product', {
    snippets: {
        'de-DE': deDE,
        'en-GB': enGB
    }
});
