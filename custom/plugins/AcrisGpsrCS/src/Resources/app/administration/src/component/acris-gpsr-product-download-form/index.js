import template from './acris-gpsr-product-download-form.html.twig';
import './acris-gpsr-product-download-form.scss';

const { Component, Utils, Mixin } = Shopware;
const { isEmpty } = Utils.types;const { mapGetters } = Shopware.Component.getComponentHelper();

Component.register('acris-gpsr-product-download-form', {
    template,
    inject: ['repositoryFactory'],

    mixins: [
        Mixin.getByName('notification')
    ],

    props: {
        gpsrType: {
            type: String,
            required: false,
            default: "warning_note"
        },
        disabled: {
            type: Boolean,
            required: false,
            default: false
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
    },

    data() {
        return {
            editDownloadItem: null,
            downloadDefaultFolderId: null,
            showDownloadModal: false,
            isMediaLoading: false,
            columnCount: 7,
            columnWidth: 90,
            displayEditDownload: false
        };
    },

    computed: {
        downloadDefaultFolderCriteria() {
            const criteria = new Criteria(1, 1);

            criteria.addAssociation('folder');
            criteria.addFilter(Criteria.equals('entity', 'acris_gpsr_p_d'));

            return criteria;
        },
        product() {
            const state = Shopware.State.get('swProductDetail');

            if (this.isInherited) {
                return state.parentProduct;
            }

            return state.product;
        },

        downloadItems() {
            const downloadItems = this.productDownloads.slice();
            const placeholderCount = this.getPlaceholderCount(this.columnCount);
            const gpsrDocument = [];
            downloadItems.forEach((element) => {
                if(!element.gpsrType) {
                    element.gpsrType = this.gpsrType;
                }
                if(element.gpsrType === this.gpsrType) {
                    gpsrDocument.push(element);
                }
            });

            if (placeholderCount === 0) {
                return gpsrDocument;
            }

            for (let i = 0; i < placeholderCount; i += 1) {
                gpsrDocument.push(this.createPlaceholderMedia(gpsrDocument));
            }

            return gpsrDocument;
        },

        ...mapGetters('swProductDetail', {
            isStoreLoading: 'isLoading'
        }),

        isLoading() {
            return this.isMediaLoading || this.isStoreLoading;
        },

        productDownloadsRepository() {
            return this.repositoryFactory.create('acris_gpsr_p_d');
        },

        productDownloads() {
            if (!this.product || !this.product.extensions) {
                return [];
            }
            return this.product.extensions.acrisGpsrDownloads;
        },

        gridAutoRows() {
            return `grid-auto-rows: ${this.columnWidth}`;
        },

        displayEditDownload(displayEditDownload) {
            if(!displayEditDownload) displayEditDownload = this.displayEditDownload;
            return true;
        },

        productDownloadsEntityName() {
            return 'acris_gpsr_p_d';
        }
    },

    methods: {
        createdComponent() {
            this.getDownloadDefaultFolderId().then((downloadDefaultFolderId) => {
                this.downloadDefaultFolderId = downloadDefaultFolderId;
            });
        },
        onCloseDownloadModal() {
            this.showDownloadModal = false;
        },
        onOpenMedia() {
            this.showDownloadModal = true;
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
            // on replace
            if (this.product.extensions.acrisGpsrDownloads.find((productDownloads) => productDownloads.mediaId === targetId)) {
                return;
            }

            const productDownloads = this.createDownloadAssociation(targetId);

            this.product.extensions.acrisGpsrDownloads.add(productDownloads);
        },

        createDownloadAssociation(targetId) {
            const productDownloads = this.productDownloadsRepository.create(Shopware.Context.api);

            productDownloads.productId = this.product.id;
            productDownloads.mediaId = targetId;
            productDownloads.gpsrType = this.gpsrType;
            if (this.product.extensions.acrisGpsrDownloads.length <= 0) {
                productDownloads.position = 0;
            } else {
                productDownloads.position = this.product.extensions.acrisGpsrDownloads.length;
            }

            return productDownloads;
        },

        onUploadFailed(uploadTask) {
            const toRemove = this.product.extensions.acrisGpsrDownloads.find((productDownloads) => {
                return productDownloads.mediaId === uploadTask.targetId;
            });
            if (toRemove) {
                this.product.extensions.acrisGpsrDownloads.remove(toRemove.id);
            }
            this.product.isLoading = false;
        },

        removeFile(downloadItem) {
            this.product.extensions.acrisGpsrDownloads.remove(downloadItem.id);
        },

        editFile(downloadItem) {
            this.editDownloadItem = downloadItem;
            this.editDownloadItem.originalFileName = downloadItem.fileName;
            this.displayEditDownload = true;
        },

        onEditDownloadSave(downloadItem) {
            let editedDownloadItem = this.product.extensions.acrisGpsrDownloads.find((productDownloads) => productDownloads.id === downloadItem.id);
            editedDownloadItem = downloadItem;
            this.displayEditDownload = false;
        },

        onEditDownloadClose() {
            this.displayEditDownload = false;
        },

        onDropDownload(dragData) {
            if (this.product.extensions.acrisGpsrDownloads.find((productDownloads) => productDownloads.mediaId === dragData.id)) {
                return;
            }

            const productDownloads = this.createDownloadAssociation(dragData.mediaItem.id);

            this.product.extensions.acrisGpsrDownloads.add(productDownloads);
        },

        onDownloadItemDragSort(dragData, dropData, validDrop) {
            if (validDrop !== true) {
                return;
            }
            this.product.extensions.acrisGpsrDownloads.moveItem(dragData.position, dropData.position);

            this.updateDownloadItemPositions();
        },

        updateDownloadItemPositions() {
            this.productDownloads.forEach((medium, index) => {
                medium.position = index;
            });
        }
    }
});
