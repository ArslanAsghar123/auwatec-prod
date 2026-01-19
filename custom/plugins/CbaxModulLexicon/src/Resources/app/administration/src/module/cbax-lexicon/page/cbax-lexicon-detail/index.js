import template from './cbax-lexicon-detail.html.twig';
import './cbax-lexicon-detail.scss';

const { Component, Mixin } = Shopware;
const { Criteria } = Shopware.Data;
const { mapPropertyErrors } = Shopware.Component.getComponentHelper();

Component.register('cbax-lexicon-detail', {
    template,

    inject: [
        'repositoryFactory'
    ],

    mixins: [
        Mixin.getByName('notification'),
		Mixin.getByName('placeholder')
    ],

	props: {
        lexiconEntryId: {
            type: String,
            required: false,
            default: null
        }
    },

	shortcuts: {
        'SYSTEMKEY+S': 'onSave',
        ESCAPE: 'onCancel'
    },

    metaInfo() {
        return {
            title: this.$createTitle(this.identifier)
        };
    },

    data() {
        return {
            entry: {},
			term: null,
            isLoading: true,
            isSaveSuccessful: false,
            currentCollection: null,
            fields: {},
            defaultMediaFolderId: null,
            keywordTestFailed: false
        };
    },

	watch: {
		lexiconEntryId() {
            this.createdComponent();
        }
    },

    created() {
		this.createdComponent();
    },

    computed: {
        assetFilter() {
            return Shopware.Filter.getByName('asset');
        },

		identifier() {
            return this.placeholder(this.entry, 'title', '');
        },

		entityDescription() {
            return this.placeholder(
                this.entry,
                'title',
                this.$tc('cbax-lexicon.detail.placeholderNewEntry')
            );
        },

		lexiconEntryRepository() {
            return this.repositoryFactory.create('cbax_lexicon_entry');
        },

		lexiconProductRepository() {
            return this.repositoryFactory.create('cbax_lexicon_product');
        },

        defaultFolderRepository() {
            return this.repositoryFactory.create('media_default_folder');
        },

		lexiconIsLoading() {
            return this.isLoading || (Object.entries(this.entry).length === 0 && this.entry.constructor === Object);
        },

        saleschanneslIsLoaded() {
            return this.entry !== undefined && this.entry.saleschannels !== undefined;
        },

        /**
         * nur Hauptprodukte filtern und zusätzlich Hersteller-Daten laden
         * @returns {Criteria}
         */
        productCriteria() {
            const productCriteria = new Criteria(1, 10);

            productCriteria.addAssociation('manufacturer');
            productCriteria.addFilter(Criteria.equals('parentId', null));

            return productCriteria;
        },

        /**
         * SalesChannel auf nur Storefront filtern
         * @returns {Criteria}
         */
        salesChannelCriteria() {
            const defaultStorefrontId = '8A243080F92E4C719546314B577CF82B';
            const criteria = new Criteria();

            criteria.addFilter(Criteria.equals('typeId', defaultStorefrontId));

            return criteria;
        },

        productColumns() {
            return [
                {
                    property: 'name',
                    label: this.$tc('cbax-lexicon.detail.labelColumnProductName'),
                    dataIndex: 'name',
                    routerLink: 'sw.product.detail'
                }, {
                    property: 'manufacturer.name',
                    label: this.$tc('cbax-lexicon.detail.labelColumnManufacturerName'),
                    routerLink: 'sw.manufacturer.detail'
                }, {
                    property: 'productNumber',
                    label: this.$tc('cbax-lexicon.detail.labelColumnProductNumber'),
                    dataIndex: 'productNumber',
                }
            ];
        },

        manufacturerColumn() {
            return 'column-manufacturer.name';
        },

		tooltipSave() {
            const systemKey = this.$device.getSystemKey();

            return {
                message: `${systemKey} + S`,
                appearance: 'light'
            };
        },

		tooltipCancel() {
            return {
                message: 'ESC',
                appearance: 'light'
            };
        },

        mediaUploadTag() {
            return `cbax-lexicon-detail--${this.entry.id}`;
        },

        //...mapApiErrors('entry', ['title', 'keyword', 'date'])
        ...mapPropertyErrors('entry', ['title', 'keyword', 'date'])
    },

    methods: {
		createdComponent() {
		    // Id vom CMS Ordner laden
            this.getDefaultFolderId().then((folderId) => {
                this.defaultMediaFolderId = folderId;
            });

            // Daten laden, wenn kein neuer Eintrag
            if (this.lexiconEntryId) {
                this.loadEntityData();
				return;
            }

            // zur Standardsprache wechseln
            Shopware.State.commit('context/resetLanguageToDefault');

            // neuen Eintrag anlegen
            this.entry = this.lexiconEntryRepository.create();

            this.entry['productLimit'] = 24;

            this.isLoading = false;
        },

        loadEntityData() {
            const criteria = new Criteria();

            criteria.addAssociation('saleschannels.salesChannel');
            criteria.addAssociation('products');

            this.lexiconEntryRepository.get(this.lexiconEntryId, Shopware.Context.api, criteria).then((entity) => {
                this.entry = entity;
                this.setInitValues();
                this.isLoading = false;
			});
        },

        updateSalesChannels(item) {
		    this.entry.saleschannels = item;
        },

        onSave() {
            this.isLoading = true;
            this.setDefaults();

            if (this.entry.title && this.entry.keyword && this.entry.saleschannels && this.entry.saleschannels.length > 0) {
                const initContainer = Shopware.Application.getContainer('init');
                const httpClient = initContainer.httpClient;
                const loginService = Shopware.Service('loginService');

                httpClient.post('/cbax/lexicon/saveEntry', {entry: this.entry, languageId: Shopware.Context.api.languageId}, {
                    headers: {
                        Authorization: `Bearer ${loginService.getToken()}`,
                    }
                }).then((responseSave) => {
                    if (responseSave.data !== undefined && responseSave.data['success'] === true) {
                        this.createNotificationSuccess({
                            title: this.$t('cbax-lexicon.notification.titleSaveSuccess'),
                            message: this.$t('cbax-lexicon.notification.messageSaveSuccess')
                        });

                        if (this.lexiconEntryId === null && responseSave.data['id'] !== undefined) {
                            this.$nextTick(() => {
                                this.$router.push({ name: 'cbax.lexicon.detail', params: { id: responseSave.data['id'] } });
                            });
                        } else {
                            this.loadEntityData();
                        }

                    } else if (responseSave.data !== undefined && responseSave.data['error'] !== undefined) {
                        let message = this.$t('cbax-lexicon.notification.messageSaveError');

                        if (responseSave.data['error'].startsWith('Database error:')) {
                            message = responseSave.data['error'];
                        } else if (responseSave.data['error'].length > 0) {
                            message = responseSave.data['error'] + this.$t('cbax-lexicon.notification.messageKeywordSaveError');
                        }

                        this.createNotificationError({
                            title: this.$t('cbax-lexicon.notification.titleSaveError'),
                            message: message
                        });
                    } else {

                        this.createNotificationError({
                            title: this.$t('cbax-lexicon.notification.titleSaveError'),
                            message: this.$t('cbax-lexicon.notification.messageSaveError')
                        });
                    }

                    this.isLoading = false;
                }).catch((err) => {
                    this.isLoading = false;

                    this.createNotificationError({
                        title: this.$tc('cbax-lexicon.notification.titleSaveError'),
                        message: this.$tc('cbax-lexicon.notification.messageSaveError'),
                    });
                });
            } else if (this.entry.title && this.entry.keyword) {
                this.lexiconEntryRepository.save(this.entry).then((response) => {
                    this.isLoading = false;
                    this.isSaveSuccessful = true;

                    this.createNotificationSuccess({
                        title: this.$t('cbax-lexicon.notification.titleSaveSuccess'),
                        message: this.$t('cbax-lexicon.notification.messageSaveSuccess')
                    });

                    if (this.lexiconEntryId === null) {
                        this.$router.push({ name: 'cbax.lexicon.detail', params: { id: this.entry.id } });
                    } else {
                        this.loadEntityData();
                    }
                }).catch((exception) => {
                    this.isLoading = false;

                    this.createNotificationError({
                        title: this.$tc('cbax-lexicon.notification.titleSaveError'),
                        message: this.$tc('cbax-lexicon.notification.messageSaveError'),
                    });
                });
            } else {
                this.isLoading = false;

                this.createNotificationError({
                    title: this.$tc('cbax-lexicon.notification.titleSaveError'),
                    message: this.$tc('cbax-lexicon.notification.messageSaveError'),
                });
            }
        },

        saveFinish() {
            this.isSaveSuccessful = false;
        },

		abortOnLanguageChange() {
            return this.lexiconEntryRepository.hasChanges(this.entry);
        },

		saveOnLanguageChange() {
            return this.onSave();
        },

        onChangeLanguage() {
            this.loadEntityData();
        },

		onCancel() {
            this.$router.push({ name: 'cbax.lexicon.index' });
        },

        setMediaItem({ targetId }, field) {
            this.entry[field] = targetId;
            if (field === 'media2Id') this.entry.attribute2 = targetId;
            if (field === 'media3Id') this.entry.attribute3 = targetId;
        },

        // Drop-Event
        onDropMedia(dragData, field) {
            this.setMediaItem({ targetId: dragData.id }, field);
        },
        onUnlinkMedia(field) {
            this.entry[field] = null;
            if (field === 'media2Id') this.entry.attribute2 = null;
            if (field === 'media3Id') this.entry.attribute3 = null;
        },

        onChangeGridTerm(that) {
            that.gridCriteria.term = null;
        },

        /**
         * FolderId vom CMS Ordner holen
         * @returns {null|defaultFolderId}
         */
        getDefaultFolderId() {
            const criteria = new Criteria(1, 1);
            criteria.addAssociation('folder');
            criteria.addFilter(Criteria.equals('entity', 'cms_page'));

            return this.defaultFolderRepository.search(criteria).then((searchResult) => {
                const defaultFolder = searchResult.first();
                if (defaultFolder.folder.id) {
                    return defaultFolder.folder.id;
                }

                return null;
            });
        },

        setDefaults() {
            // Leerzeichen aus String entfernen
            const trimFields = ['title', 'keyword', 'description', 'descriptionLong', 'linkDescription', 'metaTitle', 'metaKeywords', 'metaDescription', 'headline', 'attribute1', 'attribute4', 'attribute5', 'attribute6'];

            trimFields.forEach(field => {
                if (this.entry[field] !== undefined && this.entry[field] !== null) {
                    this.entry[field] = this.entry[field].trim();
                }
                if (this.entry.translated !== undefined && this.entry.translated[field] !== null) {
                    this.entry.translated[field] = this.entry.translated[field].trim();
                }
            });

            // Einstelldatum setzen, wenn leer beim Speichern
            if (this.entry.date === undefined || this.entry.date === null) {
                this.entry.date = new Date();
            }
        },

        setInitValues() {
            // Listentyp setzen
            if (this.entry.listingType === undefined || this.entry.listingType === null) {
                this.entry.listingType = 'selected_article';
            }

            // Product Layout setzen
            if (this.entry.productLayout === undefined || this.entry.productLayout === null) {
                this.entry.productLayout = 'standard';
            }

			// Product Template setzen
            if (this.entry.productTemplate === undefined || this.entry.productTemplate === null) {
                this.entry.productTemplate = 'listing_3col';
            }

			// Product Slider Breite setzen
            if (this.entry.productSliderWidth === undefined || this.entry.productSliderWidth === null) {
                this.entry.productSliderWidth = '250px';
            }

			// Product Sorting setzen
            if (this.entry.productSorting === undefined || this.entry.productSorting === null) {
                this.entry.productSorting = 'name_asc';
            }

			// Product Limit setzen
            if (this.entry.productLimit === undefined) {
                this.entry.productLimit = 24;
            }
        }
    }
});
