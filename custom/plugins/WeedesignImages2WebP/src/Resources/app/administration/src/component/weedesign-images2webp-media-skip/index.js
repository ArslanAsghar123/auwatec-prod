const { Component, Mixin } = Shopware;
import template from './weedesign-images2webp-media-skip.html.twig';

Component.register('weedesign-images2webp-media-skip', {
    template,

    props: ['label'],
    inject: ['WeedesignImages2WebPMediaSkipApiService'],

    mixins: [
        Mixin.getByName('notification')
    ],

    data() {
        return {
            isLoading: false,
            isSaveSuccessful: false,
        };
    },

    methods: {
        saveFinish() {
            this.isSaveSuccessful = false;
        },

        check() {
            this.isLoading = true;
            var me = this;
            this.WeedesignImages2WebPMediaSkipApiService.check().then((res) => {
                this.createNotificationSuccess({
                    title: this.$tc('weedesign-images2webp.media.skip.title'),
                    message: this.$tc('weedesign-images2webp.media.skip.success')
                });
                this.isLoading = false;
                this.isSaveSuccessful = false;
            });
        }
    }
});