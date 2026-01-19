import template from './footer-kit-module-create.html.twig';

const {Component, Mixin} = Shopware;
const {Criteria} = Shopware.Data;

Component.register('footer-kit-module-create', {
    template,
    inject: [
        'repositoryFactory'
    ],
    mixins: [
        Mixin.getByName('notification')
    ],
    data() {
        return {
            isLoading: false,
            footerKit: null,
        };
    },

    computed: {
        footerKitRepository() {
            return this.repositoryFactory.create('cogi_footer_kit');
        },
    },

    created() {
        this.createFooterKit();
    },

    methods: {
        onFooterKitSave(){
            this.footerKit.navigationConfig = {
                'verticalSpacing': '',
                'horizontalSpacing': '',
                'backgroundColor': '',
                'fontColor': '',
                'active': false,
                'serviceHotline': false,
                'fontColorLink': '',
                'transitionTime': '',
                'backgroundImage': '',
                'backgroundImageFit': '',
                'backgroundImageSize': '',
                'backgroundImageRepeat': '',
                'backgroundImagePosition': '',
            };

            this.footerKit.informationConfig = {
                "basicSettings": {
                    "active": false,
                    "center": false,
                    "titleColor": "",
                    "productType": "",
                    "backgroundColor": "",
                    "informationType": "custom",
                    "salesChannelNew": "",
                    "verticalSpacing": "",
                    "horizontalSpacing": "",
                    "numberOfNewProduct": "6",
                    "salesChannelCustom": ""
                },
                "dynamicProductSettings": {
                    "productIds": []
                }
            };

            this.footerKit.paymentShippingConfig = {
                "fontColor": "",
                "paymentActive": false,
                "shippingActive": false,
                "backgroundColor": "",
                "verticalSpacing": "",
                "horizontalSpacing": ""
            };

            this.footerKit.bottomConfig = {
                "socialMedia": [],
                "basicSettings": {
                    "active": false,
                    "vatAktive": false,
                    "vatFontColor": "",
                    "backgroundColor": "",
                    "vatVerticalSpacing": "",
                    "vatHorizontalSpacing": ""
                },
                "customLinkSettings": {
                    "tab": false,
                    "active": false,
                    "fontSize": "",
                    "fontColor": "",
                    "fontColorHover": "",
                    "transitionTime": "",
                    "verticalSpacing": "",
                    "horizontalSpacing": ""
                },
                "companyLogoSettings": {
                    "size": "",
                    "media": "",
                    "active": false,
                    "verticalSpacing": "",
                    "horizontalSpacing": ""
                },
                "socialMediaSettings": {
                    "tab": false,
                    "active": false,
                    "iconSize": "",
                    "fontColor": "",
                    "verticalSpacing": "",
                    "horizontalSpacing": ""
                }
            };

            this.footerKitRepository.save(this.footerKit, Shopware.Context.api).then(() => {
                this.isLoading = false;
                this.processSuccess = true;
                this.createNotificationSuccess({
                    title: this.$tc('global.default.success'),
                    message: this.$tc('footer-kit.create.messageCreateSuccess')
                });
                this.$router.push({name: 'footer.kit.module.overview', params: { id: this.footerKit.id }});
            })
            .catch((exception) => {
                this.isLoading = false;

                this.createNotificationError({
                    title: this.$tc('global.default.error'),
                    message: exception
                });
            });

        },

        createFooterKit(){
            this.footerKit = this.footerKitRepository.create();
        },

        backToRoute(){
            this.$router.push({name: 'footer.kit.module.list'});
        }
    }
});