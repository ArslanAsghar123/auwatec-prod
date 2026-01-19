import template from './acris-gpsr-edit-download-modal.html.twig';
import './acris-gpsr-edit-download-modal.scss';
import deDE from "../../module/acris-gpsr-note/snippet/de-DE.json";
import enGB from "../../module/acris-gpsr-note/snippet/en-GB.json";

const { Component } = Shopware;
const { Criteria } = Shopware.Data;

Component.register('acris-gpsr-edit-download-modal', {
    template,

    inject: ['repositoryFactory'],
    snippets: {
        'de-DE': deDE,
        'en-GB': enGB,
    },
    props: {
        downloadItem: {
            type: Object,
            required: true
        }
    },

    data() {
        return {
            mediaModalIsOpen: false,
            tabsTotal: 0,
            isLoading: false
        }
    },

    computed: {


        uploadTag() {
            return `cms-element-media-config-${this.downloadItem.id}`;
        },

        previewSource() {
            if (this.downloadItem && this.downloadItem.previewMedia && this.downloadItem.previewMediaId) {
                return this.downloadItem.previewMedia;
            }

            return this.downloadItem.previewMediaId;
        },
    },

    methods: {


        onCancel() {
            this.downloadItem.fileName = this.downloadItem.originalFileName;
            this.$emit('modal-close');
        },

        onApply() {
            this.$emit('modal-save', this.downloadItem);
        },
        onOpenMediaModal() {
            this.mediaModalIsOpen = true;
        },
        onImageRemove() {
            this.downloadItem.previewMediaId = null;
            this.downloadItem.previewMedia = [];

            this.$emit('element-update', this.downloadItem);
        },
        onImageUpload({ targetId }) {
            this.mediaRepository.get(targetId, Shopware.Context.api).then((mediaEntity) => {
                this.downloadItem.previewMediaId = mediaEntity.id;
                this.downloadItem.previewMedia = mediaEntity;

                this.$emit('element-update', this.downloadItem);
            });
        },
        onCloseModal() {
            this.downloadItem.fileName = this.downloadItem.originalFileName;
            this.mediaModalIsOpen = false;
        },
        onSelectionChanges(mediaEntity) {
            const media = mediaEntity[0];
            this.downloadItem.previewMediaId = media.id;
            this.downloadItem.previewMedia = media;

            this.$emit('element-update', this.downloadItem);
        },
    }
});
