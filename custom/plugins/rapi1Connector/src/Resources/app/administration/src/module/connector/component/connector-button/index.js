import template from './template.html.twig';
import './style.scss';

const {Component} = Shopware;

Component.register('rapidmail-connector-button', {
    template,
    inject: [
        'userService',
    ],
    data() {
        return {
            url: null,
            connection: null,
            shopConnectionUrl: null,
            overviewUrl: null,
            payload: null,
            signup: false,
        };
    },
    computed: {
        loading() {
            return !this.url || !this.payload;
        },
    },
    methods: {
        onSubmit() {
            if (this.url && this.payload && this.shopConnectionUrl && this.shopInfo) {
                return this.$refs.form.submit();
            }

            const headers = this.userService.getBasicHeaders();

            this.userService.httpClient
                .post('/rapidmail/connection/credentials', {}, {headers})
                .then(response => {
                    const res = Shopware.Classes.ApiService.handleResponse(response);

                    this.url = res.url;
                    this.connection = res.connection;
                    this.payload = res.payload;
                    this.signup = false;

                    this.$nextTick(() => {
                        this.$refs.form.submit();
                    });
                });
        },
        onSignup() {
            const headers = this.userService.getBasicHeaders();
            return this.userService.httpClient
                .post('/rapidmail/connection/credentials', {}, {headers})
                .then(response => {
                    const res = Shopware.Classes.ApiService.handleResponse(response);

                    this.url = res.url;
                    this.connection = res.connection;
                    this.payload = res.payload;
                    this.signup = true;

                    this.$nextTick(() => {
                        this.$refs.form.submit();
                    });
                });
        },
    },
});
