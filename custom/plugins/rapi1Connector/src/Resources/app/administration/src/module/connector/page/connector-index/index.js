import template from './template.html.twig';
import './style.scss';

const {Component} = Shopware;

Component.register('rapi1-connector-index', {
  template,
  inject: [
    'userService',
  ],
  data() {
    return {
      loaded: false,
      overviewUrl: null,
    };
  },
  created() {
    const headers = this.userService.getBasicHeaders();

    return this.userService.httpClient.get('/rapidmail/connection/info', {headers})
      .then(response => {
        const res = Shopware.Classes.ApiService.handleResponse(response);
        this.overviewUrl = res.overviewUrl;
        this.loaded = true;
      });
  },
});
