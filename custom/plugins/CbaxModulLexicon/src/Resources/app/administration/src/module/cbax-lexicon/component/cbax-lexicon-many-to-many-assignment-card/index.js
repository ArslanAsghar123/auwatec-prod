const { Component } = Shopware;
const { Criteria } = Shopware.Data;


Component.extend('cbax-lexicon-many-to-many-assignment-card', 'sw-many-to-many-assignment-card', {

    methods: {
        setGridFilter() {
            this.gridCriteria.term = this.searchTerm || null;
            
            this.$emit('cbax-lexicon-grid-term-change', this);
            
            this.addContainsFilter(this.gridCriteria);
        }
    }
});
