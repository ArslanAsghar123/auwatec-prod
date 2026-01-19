import template from './sw-cms-detail.html.twig';

const { Component } = Shopware;
const { Criteria } = Shopware.Data;

Component.override('sw-cms-detail', {
    template,

    computed: {

        cbaxLexiconEntryRepository() {
            return this.repositoryFactory.create('cbax_lexicon_entry');
        },

        cmsPageTypeSettings() {
            if (this.page.type === 'cbax_lexicon') {
                return {
                    entity: 'cbax_lexicon_entry',
                    mode: 'single'
                };
            } else {
                return this.$super('cmsPageTypeSettings');
            }
        }
    },

    methods: {

        loadFirstDemoEntity() {

            this.$super('loadFirstDemoEntity');

            if (this.cmsPageState.currentMappingEntity === 'cbax_lexicon_entry') {

                Shopware.State.commit('cmsPageState/removeCurrentDemoEntity');

                const criteria = new Criteria();
                criteria.addAssociation('media2');
                criteria.addAssociation('media3');
                criteria.limit = 1;

                this.cbaxLexiconEntryRepository.search(criteria).then((response) => {
                    if (response.first()) {
                        let demo = response.first();
                        this.demoEntityId = demo.id;
                        Shopware.State.commit('cmsPageState/setCurrentDemoEntity', demo);
                    }
                });
            }

        }
    }

});
