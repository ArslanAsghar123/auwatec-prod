
import './module/aku-cms-factory-element';
import './module/sw-cms/blocks/text/aku-cms-factory-block';
import './module/sw-cms/elements/aku-cms-factory';

import deDE from './module/aku-cms-factory-element/snippet/de-DE.json';
import enGB from './module/aku-cms-factory-element/snippet/en-GB.json';

import CMSdeDE from './module/sw-cms/snippet/de-DE.json';
import CMSenGB from './module/sw-cms/snippet/en-GB.json';

Shopware.Locale.extend('de-DE', deDE);
Shopware.Locale.extend('en-GB', enGB);

Shopware.Locale.extend('de-DE', CMSdeDE);
Shopware.Locale.extend('en-GB', CMSenGB);
