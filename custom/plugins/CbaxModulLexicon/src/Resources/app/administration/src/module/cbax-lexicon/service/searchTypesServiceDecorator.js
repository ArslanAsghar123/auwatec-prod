const { Application } = Shopware;

Application.addServiceProviderDecorator('searchTypeService', searchTypeService => {
    searchTypeService.upsertType('cbax_lexicon_entry', {
        entityName: 'cbax_lexicon_entry',
        entityService: 'cbaxLexiconEntryService',
        placeholderSnippet: 'cbax-lexicon.general.placeholderSearchBar',
        listingRoute: 'cbax.lexicon.index'
    });

    return searchTypeService;
});
