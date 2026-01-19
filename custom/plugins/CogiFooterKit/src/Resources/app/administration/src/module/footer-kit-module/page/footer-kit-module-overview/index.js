import template from './footer-kit-module-overview.html.twig';
import './footer-kit-module-overview.scss';

const { Component, Mixin, Context } = Shopware;
const { EntityCollection, Criteria } = Shopware.Data;

Component.register('footer-kit-module-overview', {
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
            processSuccess: false,
            repository: null,
            salesChannelId: null,
            footerKitName: "",
            navigationConfig: [],
            informationConfig: [],
            paymentShippingConfig: [],
            bottomConfig: [],
            translated: {
                navigationBlock: [],
                informationBlock: [],
                customLink: [],
                shippingString: "",
                paymentString: "",
                socialMediaString: "",
                productSliderTitle: "",
            },
            footerKit: null,
            footerKitId: null,
            socialMediaInput: [],
            backgroundType: "color",
            product: null
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    created() {
        this.repository = this.repositoryFactory.create('cogi_footer_kit');
        // const criteria = new Criteria();
        // this.repository.search(criteria, Shopware.Context.api).then((result) => {
        //     this.footerKitId =result[0].id;
        // })
        this.getFooterKit();
    },

    computed: {
        CriteriaSalesChannel(){
            const criteria = new Criteria();
            criteria.addFilter(Criteria.equals('typeId', '8A243080F92E4C719546314B577CF82B'));

            return criteria;
        },


        CriteriaProduct() {
            const criteria = new Criteria();
            criteria.addAssociation('visibilities');
            criteria.addFilter(Criteria.equals('visibilities.salesChannelId', this.informationConfig.basicSettings.salesChannelCustom));

            return criteria;
        },

        productRepository() {
            return this.repositoryFactory.create('product');
        },

        columnValue() {
            return [
                {
                    label: "1/4",
                    value: 'col-sm-12 col-md-6 col-xl-3'
                },
                {
                    label: "2/4",
                    value: 'col-sm-12 col-md-9 col-xl-6'
                },
                {
                    label: "3/4",
                    value: 'col-sm-12 col-md-12 col-xl-9'
                },
                {
                    label: "4/4",
                    value: 'col-sm-12 col-md-12 col-xl-12'
                }
            ];
        },

        backgroundTypeValue() {
            return [
                {
                    label: this.$tc('footer-kit.label.image'),
                    value: 'image'
                },
                {
                    label: this.$tc('footer-kit.label.color'),
                    value: 'color'
                }
            ];
        },

        backgroundImageRepeatValue() {
            return [
                {
                    label: this.$tc('footer-kit.repeatValue.repeat'),
                    value: 'repeat'
                },
                {
                    label: this.$tc('footer-kit.repeatValue.no-repeat'),
                    value: 'no-repeat'
                },
                {
                    label: this.$tc('footer-kit.repeatValue.repeat-x'),
                    value: 'repeat-x'
                },
                {
                    label: this.$tc('footer-kit.repeatValue.repeat-y'),
                    value: 'repeat-y'
                },
            ];
        },

        backgroundPositionValue() {
            return [
                {
                    label: this.$tc('footer-kit.background.positionCenter'),
                    value: 'center'
                },
                {
                    label: this.$tc('footer-kit.background.positionTop'),
                    value: 'top'
                },
                {
                    label: this.$tc('footer-kit.background.positionRight'),
                    value: 'right'
                },
                {
                    label: this.$tc('footer-kit.background.positionBottom'),
                    value: 'bottom'
                },
                {
                    label: this.$tc('footer-kit.background.positionLeft'),
                    value: 'left'
                },
            ];
        },

        backgroundFitValue() {
            return [
                {
                    label: this.$tc('footer-kit.fit.cover'),
                    value: 'cover'
                },
                {
                    label: this.$tc('footer-kit.fit.contain'),
                    value: 'contain'
                },
                {
                    label: this.$tc('footer-kit.fit.auto'),
                    value: 'auto'
                },
            ];
        },

        informationTypValue() {
            return [
                {
                    label: this.$tc('footer-kit.information.custom'),
                    value: 'custom'
                },
                {
                    label: this.$tc('footer-kit.information.dynamic'),
                    value: 'dynamic'
                },
            ];
        },

        productTypeValue(){
            return [
                {
                    label: this.$tc('footer-kit.information.productSelect'),
                    value: 'select'
                },
                {
                    label: this.$tc('footer-kit.information.productNew'),
                    value: 'new'
                }
            ];
        }
    },

    methods: {
        getFooterKit(){
            this.footerKitId = this.$route.params.id;
            this.repository.get(this.footerKitId, Shopware.Context.api)
                .then((result) => {
                    this.navigationConfig = result.navigationConfig;
                    this.informationConfig = result.informationConfig;
                    this.paymentShippingConfig = result.paymentShippingConfig;
                    this.bottomConfig = result.bottomConfig;
                    this.footerKit = result;
                    this.socialMediaInput = result.bottomConfig["socialMedia"];
                    this.translated.customLink = result["customLink"];
                    this.translated.socialMediaString = result["socialMediaString"];
                    this.translated.navigationBlock = result["navigationBlock"];
                    this.translated.informationBlock = result["informationBlock"];
                    this.translated.shippingString = result["shippingString"];
                    this.translated.paymentString = result["paymentString"];
                    this.translated.productSliderTitle = result["productSliderTitle"];

                    this.salesChannelId = result.salesChannelId;
                    this.footerKitName = result.name;

                    this.product = new EntityCollection(
                        this.productRepository.route,
                        this.productRepository.entityName,
                        Context.api
                    );
        
                    if (this.informationConfig.dynamicProductSettings.productIds.length <= 0) {
                        return Promise.resolve();
                    }
        
                    const criteria = new Criteria();
                    criteria.setIds(this.informationConfig.dynamicProductSettings.productIds);
        
                    return this.productRepository.search(criteria, Context.api).then((products) => {
                        this.product = products;
                    });
                });
        },

        onClickSave() {
            this.isLoading = true;

            this.footerKit.navigationConfig = this.navigationConfig;
            this.footerKit.informationConfig = this.informationConfig;
            this.footerKit.paymentShippingConfig = this.paymentShippingConfig;
            this.footerKit.bottomConfig = this.bottomConfig;
            this.footerKit.bottomConfig["socialMedia"] = this.socialMediaInput;
            this.footerKit["informationBlock"] = this.translated["informationBlock"];
            this.footerKit["navigationBlock"] = this.translated["navigationBlock"];
            this.footerKit["customLink"] = this.translated["customLink"];
            this.footerKit["socialMediaString"] = this.translated["socialMediaString"];
            this.footerKit["shippingString"] = this.translated["shippingString"];
            this.footerKit["paymentString"] = this.translated["paymentString"];
            this.footerKit["productSliderTitle"] = this.translated["productSliderTitle"];
            this.footerKit.name = this.footerKitName;
            this.footerKit.salesChannelId = this.salesChannelId;

            this.repository.save(this.footerKit, Shopware.Context.api)
                .then(() =>{
                    this.isLoading = false;
                    this.processSuccess = true;
                    this.createNotificationSuccess({
                        title: this.$tc('global.default.success'),
                        message: this.$tc('footer-kit.save.messageSaveSuccess')
                    });
                })
                .catch((exception) => {
                    this.isLoading = false;
                
                    this.createNotificationError({
                        title: this.$tc('global.default.error'),
                        message: exception
                    });
                });
        },

        saveFinish() {
            this.processSuccess = false;
            this.getFooterKit();
        },

        addRowCustomLink(limit) {

            if(this.translated.customLink == null){
                this.translated.customLink = [];
            }

            if(this.translated.customLink.length <= limit){
                this.translated.customLink.push({
                    name: '',
                    url: '',
                    active: true
                }); 
            } else {
                this.createNotificationError({
                    title: this.$tc('global.default.error'),
                    message: this.$tc('footer-kit.message.addFail')
                });
            }
        },

        deleteRowCustomLink(index) {
            this.translated.customLink.splice(index,1);
        },

        addRowInformationBlock(limit) {

            if(typeof this.translated.informationBlock == 'object' && this.translated.informationBlock != null){
                if(Object.keys(this.translated.informationBlock).length === 0){
                    this.translated.informationBlock = [];
                }
            }

            if(this.translated.informationBlock == null){
                this.translated.informationBlock = [];
            }

            if(this.translated.informationBlock.length <= limit){
                this.translated.informationBlock.push({
                    block: '',
                    active: true,
                    col: 'col-sm-12 col-md-6 col-xl-3'
                });
            } else {
                this.createNotificationError({
                    title: this.$tc('global.default.error'),
                    message: this.$tc('footer-kit.message.addFail')
                });
            }
        },

        deleteRowInformationBlock(index) {
            this.translated.informationBlock.splice(index,1);
        },

        addRowNavigationBlock(limit) {

            if(typeof this.translated.navigationBlock === 'object' && this.translated.navigationBlock != null){
                if(Object.keys(this.translated.navigationBlock).length === 0){
                    this.translated.navigationBlock = [];
                }
            }

            if(this.translated.navigationBlock == null){
                this.translated.navigationBlock = [];
            }



            if(this.translated.navigationBlock.length <= limit){
                this.translated.navigationBlock.push({
                    title: '',
                    block: '',
                    active: true,
                    col: 'col-sm-12 col-md-6 col-xl-3'
                });
            } else {
                this.createNotificationError({
                    title: this.$tc('global.default.error'),
                    message: this.$tc('footer-kit.message.addFail')
                });
            }
        },

        deleteRowNavigationBlock(index) {
            this.translated.navigationBlock.splice(index,1);
        },

        addRowSocialMedia(limit){
            if(this.socialMediaInput.length <= limit){
                this.socialMediaInput.push({
                    url: '',
                    media: '',
                    active: true
                });
            } else {
                this.createNotificationError({
                    title: this.$tc('global.default.error'),
                    message: this.$tc('footer-kit.message.addFail')
                });
            }
        },

        deleteRowSocialMedia(index) {
            this.socialMediaInput.splice(index, 1);
        },

        onChangeLanguage(languageId) {
            Shopware.State.commit('context/setApiLanguageId', languageId);
            this.getFooterKit();
        },

        setProductIds(products) {
            this.informationConfig.dynamicProductSettings.productIds = products.getIds();
            this.product = products;
        },

        backToRoute(){
            this.$router.push({name: 'footer.kit.module.list'});
        }
    }
})