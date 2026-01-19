const { Component, Mixin } = Shopware;
import template from './weedesign-images2webp-media-generate.html.twig';

Component.register('weedesign-images2webp-media-generate', {
    template,

    props: ['label'],
    inject: ['WeedesignImages2WebPMediaGenerateApiService'],

    mixins: [
        Mixin.getByName('notification')
    ],

    data() {
        return {
            isLoading: false,
            isSaveSuccessful: false,
            scan: null,
            images: 0,
            webp: 0
        };
    },

    created() {
        this.createComponent();
    },

    methods: {
        saveFinish() {
            this.isSaveSuccessful = false;
        },

        check() {
            this.isLoading = true;
            this.WeedesignImages2WebPMediaGenerateApiService.check().then((res) => {
                this.info(res,false);
            });
        },

        all() {
            this.isLoading = true;
            this.WeedesignImages2WebPMediaGenerateApiService.all().then((res) => {
                this.info(res,true);
            });
        },

        scanImages() {
            this.isLoading = true;
            this.WeedesignImages2WebPMediaGenerateApiService.scan().then((res) => {
                if (typeof(res.images)!=typeof(this_is_not_defined)) {
                    if(res.images*1>this.images*1) {
                        this.images = res.images;
                        document.getElementById("WeedesignImages2WebPMediaProgressImages").value = this.images;
                        this.scan = false;
                        this.createNotificationSuccess({
                            title: this.$tc('weedesign-images2webp.media.generate.scan.button'),
                            message: this.$tc('weedesign-images2webp.media.generate.scan.new')
                        });
                        document.getElementById("WeedesignImages2WebPMediaProgressFinish").value = 0;
                    } else {
                        this.createNotificationSuccess({
                            title: this.$tc('weedesign-images2webp.media.generate.scan.button'),
                            message: this.$tc('weedesign-images2webp.media.generate.scan.nope')
                        });
                    }
                }
                this.isLoading = false;
                this.isSaveSuccessful = true;
            });
        },

        progress() {
            var me = this;
            if(typeof(document.getElementById("WeedesignImages2WebPMediaProgressUpdate"))!==typeof(this_is_not_defined)) {
                var update = document.getElementById("WeedesignImages2WebPMediaProgressUpdate");
                if(update.name=="WeedesignImages2WebPMediaProgressUpdate") {
                    this.images = document.getElementById("WeedesignImages2WebPMediaProgressImages").value;
                    this.webp = update.value;
                    this.scan = false;
                    if(document.getElementById("WeedesignImages2WebPMediaProgressImages").value==update.value) {
                        if(update.value==0) {
                            this.scan = 2;
                        } else {
                            this.scan = 3;
                            document.getElementById("WeedesignImages2WebPMediaProgressFinish").value = 1;
                        }
                    }
                    window.setTimeout(function() {
                        me.progress();
                    },1500);
                } else {
                    window.setTimeout(function() {
                        me.progress();
                    },250);
                }
            } else {
                window.setTimeout(function() {
                    me.progress();
                },250);
            }
        },

        repeat() {
            var me = this;
            window.setTimeout(function() {
                me.all();
            },1000);
        },

        info(res,all) {
            if(typeof(res)=="string") {
                if(res.indexOf('}{"errors"')>-1) {
                    var resBug = res.split('{"errors');
                    res = JSON.parse(resBug[0]);
                }
            }
            if (typeof(res.success)!=typeof(this_is_not_defined)) {
                this.isSaveSuccessful = true;
                if(res.success!="empty") {
                    if(res.success!==true) {
                        if(res.images==0) {
                            document.getElementById("WeedesignImages2WebPMediaProgressUpdate").value = document.getElementById("WeedesignImages2WebPMediaProgressImages").value;
                            this.scan = true;
                        } else {
                            document.getElementById("WeedesignImages2WebPMediaProgressUpdate").value = (document.getElementById("WeedesignImages2WebPMediaProgressUpdate").value*1)+(res.images*1);
                            if(res.error!=0) {
                                document.getElementById("WeedesignImages2WebPMediaProgressImages").value = (document.getElementById("WeedesignImages2WebPMediaProgressImages").value*1)-(res.error*1);
                                document.getElementById("WeedesignImages2WebPMediaProgressUpdate").value = (document.getElementById("WeedesignImages2WebPMediaProgressUpdate").value*1)-(res.error*1);
                            }
                        }
                        if(res.success=="time") {
                            this.createNotificationSuccess({
                                title: this.$tc('weedesign-images2webp.media.generate.title'),
                                message: this.$tc('weedesign-images2webp.media.generate.time').replace("[images]",res.images.toLocaleString()).replace("[time]",res.typeValue)
                            });
                        } else if(res.success=="memory") {
                            this.createNotificationSuccess({
                                title: this.$tc('weedesign-images2webp.media.generate.title'),
                                message: this.$tc('weedesign-images2webp.media.generate.memory').replace("[images]",res.images.toLocaleString()).replace("[memory]",res.typeValue)
                            });
                        } else {
                            if(res.images==0) {
                                this.createNotificationSuccess({
                                    title: this.$tc('weedesign-images2webp.media.generate.title'),
                                    message: this.$tc('weedesign-images2webp.media.generate.all')
                                });
                                this.scan = true;
                                document.getElementById("WeedesignImages2WebPMediaProgressFinish").value = 1;
                            } else {
                                this.createNotificationSuccess({
                                    title: this.$tc('weedesign-images2webp.media.generate.title'),
                                    message: this.$tc('weedesign-images2webp.media.generate.success').replace("[images]",res.images.toLocaleString())
                                });
                                if(document.getElementById("WeedesignImages2WebPMediaProgressUpdate").value==document.getElementById("WeedesignImages2WebPMediaProgressImages").value) {
                                    this.scan = true;
                                    if(document.getElementById("WeedesignImages2WebPMediaProgressUpdate").value!=0) {
                                        document.getElementById("WeedesignImages2WebPMediaProgressFinish").value = 1;
                                    }
                                }
                            }
                        }
                        if(document.getElementById("WeedesignImages2WebPMediaProgressUpdate").value==document.getElementById("WeedesignImages2WebPMediaProgressImages").value) {
                            this.scan = true;
                            if(document.getElementById("WeedesignImages2WebPMediaProgressUpdate").value!=0) {
                                document.getElementById("WeedesignImages2WebPMediaProgressFinish").value = 1;
                            }
                        }
                        if(all===true) {
                            if(res.images>0) {
                                if(document.getElementById("WeedesignImages2WebPMediaProgressUpdate").value!=document.getElementById("WeedesignImages2WebPMediaProgressImages").value) {
                                    this.repeat();
                                }
                            }
                        }
                    } else {
                        this.createNotificationSuccess({
                            title: this.$tc('weedesign-images2webp.media.generate.title'),
                            message: this.$tc('weedesign-images2webp.media.generate.all')
                        });
                        this.scan = true;
                    }
                } else {
                    this.createNotificationSuccess({
                        title: this.$tc('weedesign-images2webp.media.generate.title'),
                        message: this.$tc('weedesign-images2webp.media.generate.empty')
                    });
                }
            } else {
                this.createNotificationError({
                    title: this.$tc('weedesign-images2webp.media.generate.title'),
                    message: this.$tc('weedesign-images2webp.media.generate.error')
                });
            }
            this.isLoading = false;
            this.isSaveSuccessful = false;
        },

        createComponent() {
            var me = this;
            window.setTimeout(function() {
                me.progress();
            },250);
        },

    }
});