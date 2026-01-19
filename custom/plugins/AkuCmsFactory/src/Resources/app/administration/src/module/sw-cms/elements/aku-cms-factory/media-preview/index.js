import template from './sw-cms-media-preview-aku-cms-factory.html.twig';

const { Component, Mixin } = Shopware;
const { Criteria, EntityCollection } = Shopware.Data;

Shopware.Component.register('sw-cms-el-media-preview-aku-cms-factory', {
    template,
    props: {
        media_id: {
            type: String
        },
        height: {
            type: Number,
            default: 30,
        },
        width: {
            type: Number,
            default: 50
        }
    },
    inject: ['repositoryFactory'],
    data(){
        return {
            medium: null,
        }
    },
    computed: {
        mediaRepository() {
            return this.repositoryFactory.create('media');
        },
        getStyle() {
            return "width:" + this.width + "px;" +
                "height:" + this.height + "px;" +
                "position:relative;";
        }
    },
    methods: {
        loadMedium(){
            let that = this;
            let uuidRegex = new RegExp('^[0-9a-fA-F]{32}$');
            this.medium = null;
            if (null == this.media_id || !uuidRegex.test(String(this.media_id))) {
                return;
            }
            this.mediaRepository
                .get(this.media_id, Shopware.Context.api)
                .then((result) => {
                    that.medium = result;
                });
        }
    },
    created() {
        this.loadMedium();
    },
    watch: {
        media_id(){
            this.loadMedium();
        }
    }
});