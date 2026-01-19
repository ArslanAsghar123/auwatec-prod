const { Component, Mixin } = Shopware;
import template from './weedesign-images2webp-media-progress.html.twig';

Component.register('weedesign-images2webp-media-progress', {
    template,

    props: ['label'],
    inject: ['WeedesignImages2WebPMediaProgressApiService'],

    mixins: [
        Mixin.getByName('notification')
    ],

    data() {
        return {
            webp: 0,
            images: -2,
            webpString: 0,
            imagesString: 0,
            init: false,
            interval: false,
            finish: 0
        };
    },

    created() {
        this.createComponent();
    },

    methods: {
        createComponent() {
            
            var me = this;

            var update = document.getElementById("WeedesignImages2WebPMediaProgressUpdate");
            if(!update) {

                var WeedesignImages2WebPMediaProgressUpdateInput = document.createElement("input");
                WeedesignImages2WebPMediaProgressUpdateInput.type = "hidden";
                WeedesignImages2WebPMediaProgressUpdateInput.id = "WeedesignImages2WebPMediaProgressUpdate";
                WeedesignImages2WebPMediaProgressUpdateInput.value = 0;
                document.body.appendChild(WeedesignImages2WebPMediaProgressUpdateInput);

                var WeedesignImages2WebPMediaProgressFinishInput = document.createElement("input");
                WeedesignImages2WebPMediaProgressFinishInput.type = "hidden";
                WeedesignImages2WebPMediaProgressFinishInput.id = "WeedesignImages2WebPMediaProgressFinish";
                WeedesignImages2WebPMediaProgressFinishInput.value = 0;
                document.body.appendChild(WeedesignImages2WebPMediaProgressFinishInput);
                
                var WeedesignImages2WebPMediaProgressImagesInput = document.createElement("input");
                WeedesignImages2WebPMediaProgressImagesInput.type = "hidden";
                WeedesignImages2WebPMediaProgressImagesInput.id = "WeedesignImages2WebPMediaProgressImages";
                WeedesignImages2WebPMediaProgressImagesInput.value = 0;
                document.body.appendChild(WeedesignImages2WebPMediaProgressImagesInput);

            } else {
                var WeedesignImages2WebPMediaProgressUpdateInput = document.getElementById("WeedesignImages2WebPMediaProgressUpdate");
                var WeedesignImages2WebPMediaProgressImagesInput = document.getElementById("WeedesignImages2WebPMediaProgressImages");
            }

            this.WeedesignImages2WebPMediaProgressApiService.check().then(response => {
                if(response.success==true) {
                    WeedesignImages2WebPMediaProgressUpdateInput.value = response.webp;
                    WeedesignImages2WebPMediaProgressUpdateInput.name = "WeedesignImages2WebPMediaProgressUpdate";
                    this.webp = response.webp;
                    this.images = response.images;
                    WeedesignImages2WebPMediaProgressImagesInput.value = this.images;
                    this.webpString = response.webp.toLocaleString();
                    this.imagesString = response.images.toLocaleString();
                    this.init = false;
                } else {
                    this.images = -1;
                    this.init = true;
                }
                if(this.interval===false) {
                    this.interval = true;
                    window.setInterval(function() {
                        me.checkForUpdate();
                    },500);
                }
            });
        },
        checkForUpdate() {
            var update = document.getElementById("WeedesignImages2WebPMediaProgressUpdate");
            var update_value = update.value*1;
            if(this.webp!=update_value) {
                this.webp = update_value;
                this.webpString = update_value.toLocaleString();
            }
            var finish = document.getElementById("WeedesignImages2WebPMediaProgressFinish");
            var finish_value = finish.value*1;
            if(this.finish!=finish_value) {
                this.finish = finish_value;
            }
            var images = document.getElementById("WeedesignImages2WebPMediaProgressImages");
            var images_value = images.value*1;
            if(this.images!=images_value) {
                this.images = images_value;
                this.imagesString = images_value.toLocaleString();
            }
        }
    }
});