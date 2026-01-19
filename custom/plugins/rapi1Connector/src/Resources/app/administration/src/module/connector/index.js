import './component/connector-button';
import './page/connector-index';

import deDE from '../../../../../snippet/de-DE.json';
import enGB from '../../../../../snippet/en-GB.json';

Shopware.Module.register('rapi1-connector', {
  snippets: {
    'de-DE': deDE,
    'en-GB': enGB,
  },
  type: 'plugin',
  name: 'Rapidmail',
  title: 'Rapidmail',
  description: 'Rapidmail connector',
  color: '#ff1f44',
  icon: 'default-object-paperplane',

  routes: {
    index: {
      component: 'rapi1-connector-index',
      path: 'rapi1-connector',
    },
  },

  navigation: [
    {
      label: 'rapidmail.navigationItem',
      color: '#ff1e42',
      path: 'rapi1.connector.index',
      icon: 'default-object-paperplane',
      parent: 'sw-marketing',
    },
  ],
});
