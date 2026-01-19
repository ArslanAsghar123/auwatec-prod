import deDE from "./snippet/de-DE";
import enGB from "./snippet/en-GB";

import './page/sw-manufacturer-detail';

const { Module } = Shopware;

Module.register('acris-gpsr-manufacturer', {
    snippets: {
        'de-DE': deDE,
        'en-GB': enGB
    }
});
