import template from './acris-gpsr-product-download-form.html.twig';
import '../acris-gpsr-product-download-form/acris-gpsr-product-download-form.scss';

const { Component, Mixin, StateDeprecated } = Shopware;
const { mapGetters } = Shopware.Component.getComponentHelper();

Component.register('acris-gpsr-file-download-form', {
    template,
    inject: ['repositoryFactory'],

    mixins: [
        Mixin.getByName('notification')
    ],

    props: {
        entity: {
            type: Object,
            required: true,
        },
        extensionName: {
            type: String,
            required: true,
        },

        entityName: {
            type: String,
            required: true,
        },

        isInherited: {
            type: Boolean,
            required: false,
            default: false
        },
        acrisUploadTag: {
            type: String,
            required: false,
            default: "acrisProductDownloadsUploadTag"
        },
        repositoryName: {
            type: String,
            required: true
        }
    },

    data() {
        return {
            isMediaLoading: false,
            columnCount: 7,
            columnWidth: 90,
            disabled: false,
            displayEditDownload: false
        };
    },

    computed: {
        product() {
            return this.entity
        },

        downloadItems() {
            const downloadItems = this.productDownloads.slice();
            const placeholderCount = this.getPlaceholderCount(this.columnCount);

            if (placeholderCount === 0) {
                return downloadItems;
            }

            for (let i = 0; i < placeholderCount; i += 1) {
                downloadItems.push(this.createPlaceholderMedia(downloadItems));
            }
            return downloadItems;
        },

        ...mapGetters('swProductDetail', {
            isStoreLoading: 'isLoading'
        }),

        isLoading() {
            return this.isMediaLoading || this.isStoreLoading;
        },

        productDownloadsRepository() {
            return this.repositoryFactory.create(this.repositoryName);
        },

        productDownloads() {
            if (!this.entity || !this.entity.extensions) {
                this.entity.extensions = {}
                this.entity.extensions[this.extensionName] = [];
                return [];
            }
            return this.entity.extensions[this.extensionName];
        },

        gridAutoRows() {
            return `grid-auto-rows: ${this.columnWidth}`;
        },

        displayEditDownload(displayEditDownload) {
            if(!displayEditDownload) displayEditDownload = this.displayEditDownload;
            return true;
        },

        productDownloadsEntityName() {
            return this.entityName;
        }
    },

    methods: {
        onOpenMedia() {
            this.$emit('media-open');
        },

        updateColumnCount() {
            this.$nextTick(() => {
                if (this.isLoading) {
                    return false;
                }

                const cssColumns = window.getComputedStyle(this.$refs.grid, null)
                    .getPropertyValue('grid-template-columns')
                    .split(' ');
                this.columnCount = cssColumns.length;
                this.columnWidth = cssColumns[0];

                return true;
            });
        },

        getPlaceholderCount(columnCount) {
            if (this.productDownloads.length + 3 < columnCount * 2) {
                columnCount *= 2;
            }

            let placeholderCount = columnCount;

            if (this.productDownloads.length !== 0) {
                placeholderCount = columnCount - ((this.productDownloads.length) % columnCount);
                if (placeholderCount === columnCount) {
                    return 0;
                }
            }

            return placeholderCount;
        },

        createPlaceholderMedia(downloadItems) {
            return {
                isPlaceholder: true,
                media: {
                    isPlaceholder: true,
                    name: ''
                },
                mediaId: downloadItems.length.toString()
            };
        },

        successfulUpload({ targetId }) {

            if(!this.entity.extensions) {
                this.entity.extensions = {};
            }

            if(!this.entity.extensions[this.extensionName]) {
                this.entity.extensions[this.extensionName] = {};
            }
            // on replace
            if (this.entity.extensions[this.extensionName].find((productDownloads) => productDownloads.mediaId === targetId)) {
                return;
            }

            const productDownloads = this.createDownloadAssociation(targetId);

            this.entity.extensions[this.extensionName].add(productDownloads);
        },

        createDownloadAssociation(targetId) {
            const productDownloads = this.productDownloadsRepository.create(Shopware.Context.api);

            productDownloads.acrisGpsrContactId = this.entity.id;
            productDownloads.mediaId = targetId;
            if (this.entity.extensions[this.extensionName].length <= 0) {
                productDownloads.position = 0;
            } else {
                productDownloads.position = this.entity.extensions[this.extensionName].length;
            }

            return productDownloads;
        },

        onUploadFailed(uploadTask) {

            const toRemove = this.entity.extensions[this.extensionName].find((productDownloads) => {
                return productDownloads.mediaId === uploadTask.targetId;
            });
            if (toRemove) {
                this.entity.extensions[this.extensionName].remove(toRemove.id);
            }
            this.entity.isLoading = false;
        },

        removeFile(downloadItem) {
            this.entity.extensions[this.extensionName].remove(downloadItem.id);
        },

        editFile(downloadItem) {
            this.editDownloadItem = downloadItem;
            this.editDownloadItem.originalFileName = downloadItem.fileName;
            this.displayEditDownload = true;
        },

        onEditDownloadSave(downloadItem) {
            let editedDownloadItem = this.entity.extensions[this.extensionName].find((productDownloads) => productDownloads.id === downloadItem.id);
            editedDownloadItem = downloadItem;
            this.displayEditDownload = false;
        },

        onEditDownloadClose() {
            this.displayEditDownload = false;
        },

        onDropDownload(dragData) {
            if(!this.entity.extensions) {
                this.entity.extensions = {};
            }

            if(!this.entity.extensions[this.extensionName]) {
                this.entity.extensions[this.extensionName] = {};
            }

            if (this.entity.extensions[this.extensionName].find((productDownloads) => productDownloads.mediaId === dragData.id)) {
                return;
            }

            const productDownloads = this.createDownloadAssociation(dragData.mediaItem.id);

            this.entity.extensions[this.extensionName].add(productDownloads);
        },

        onDownloadItemDragSort(dragData, dropData, validDrop) {
            if (validDrop !== true) {
                return;
            }
            this.entity.extensions[this.extensionName].moveItem(dragData.position, dropData.position);

            this.updateDownloadItemPositions();
        },

        updateDownloadItemPositions() {
            this.productDownloads.forEach((medium, index) => {
                medium.position = index;
            });
        }
    }
});
