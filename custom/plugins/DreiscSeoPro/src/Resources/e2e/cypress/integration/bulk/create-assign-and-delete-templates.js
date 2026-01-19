describe('Create, assign and delete templates', () => {
    beforeEach(() => {
        cy.loginViaApi();
    });

    it('Create and delete own template', () => {
        cy.server();
        cy.route({
            url: '/api/v1/search/dreisc-seo-bulk-template',
            method: 'post'
        }).as('bulkTemplatesUpdated');

        cy.log('» Open the bulk module');
        cy.visit('/admin#/dreisc/seo/bulk/product/index');

        cy.log('» Load the details of the "GB-Shop" category');
        cy.get('.tree-link.category-id-2c14595e6e974658a10610d9ddcd701e')
            .click();

        cy.log('» Click the create button');
        cy.get('.dreisc-seo-bulk-detail__create-action')
            .should('be.visible')
            .click();

        cy.log('» Click the create new template button');
        cy.get('.dreisc-seo-bulk-template-list__add-button')
            .should('be.visible')
            .click();

        cy.log('» Create the template name');
        cy.get('#sw-field--currentBulkTemplate-name')
            .should('be.visible')
            .type('Test Template');

        cy.log('» Click the save button');
        cy.get('.sw-modal__footer .sw-button--primary')
            .click();

        cy.log('» Test, if the template was created and check the checkbox of the element');
        cy.wait('@bulkTemplatesUpdated').then(() => {
            cy.get('.dreisc-seo-bulk-template-list__grid')
                .contains('Test Template')
                .parents('.sw-grid-row')
                .find('.sw-field__checkbox input')
                .check();
        });

        cy.log('» Click the delete button');
        cy.get('.dreisc-seo-bulk-template-list__delete-button')
            .should('be.visible')
            .click();

        cy.log('» Click the confirm button');
        cy.get('.dreisc-seo-bulk-detail__confirm-button')
            .should('be.visible')
            .click();

        cy.log('» Wait for the update of the bulk templates');
        cy.wait('@bulkTemplatesUpdated').then(() => {
            cy.log('» Test, if the template is not displays anymore');
            cy.get('.dreisc-seo-bulk-template-list__grid')
                .should('not.contain', 'Test Template');
        });
    });

    it('Set template of the "GB-Shop" category', () => {
        cy.log('» Open the details');
        cy.visit('/admin#/dreisc/seo/bulk/product/detail/metaTitle/2fbb5fe2e29a4d70aa5854ce7ce3e20b/_/2c14595e6e974658a10610d9ddcd701e/base').then(() => {
            cy.log('» Click the create button');
            cy.get('.dreisc-seo-bulk-detail__create-action')
                .should('be.visible')
                .click();

            cy.log('» Select "Artikelname"');
            cy.get('.dreisc-seo-bulk-detail-base__field-dreiscSeoBulkTemplateId')
                .typeSingleSelect(null, { exactMatch: 'Artikelname' });

            cy.log('» Save');
            cy.get('.dreisc-seo-bulk-detail__save-action')
                .should('be.visible')
                .click();
        });
    });

    it('Set template of the "GB-Shop » Products" category', () => {
        cy.log('» Open the details');
        cy.visit('/admin#/dreisc/seo/bulk/product/detail/metaTitle/2fbb5fe2e29a4d70aa5854ce7ce3e20b/_/772290eef76d4577ad0c9bf27a04abd1/base').then(() => {
            cy.log('» Click the create button');
            cy.get('.dreisc-seo-bulk-detail__create-action')
                .should('be.visible')
                .click();

            cy.log('» Select "Artikelname"');
            cy.get('.dreisc-seo-bulk-detail-base__field-dreiscSeoBulkTemplateId')
                .typeSingleSelect(null, { exactMatch: 'Artikelname' });

            cy.log('» Save');
            cy.get('.dreisc-seo-bulk-detail__save-action')
                .should('be.visible')
                .click();
        });
    });

    it('Set template of the "GB-Shop » Products » Standard-Products" category', () => {
        cy.log('» Open the details');
        cy.visit('/admin#/dreisc/seo/bulk/product/detail/metaTitle/2fbb5fe2e29a4d70aa5854ce7ce3e20b/_/2f6b400a0b6449c9baa50831710c8a94/base').then(() => {
            cy.log('» Click the create button');
            cy.get('.dreisc-seo-bulk-detail__create-action')
                .should('be.visible')
                .click();

            cy.log('» Select "Artikelname"');
            cy.get('.dreisc-seo-bulk-detail-base__field-dreiscSeoBulkTemplateId')
                .typeSingleSelect(null, { exactMatch: 'Artikelname' });

            cy.log('» Save');
            cy.get('.dreisc-seo-bulk-detail__save-action')
                .should('be.visible')
                .click();
        });
    });

    it('Set template of the "GB-Shop » Products » GB-Products" category', () => {
        cy.log('» Open the details');
        cy.visit('/admin#/dreisc/seo/bulk/product/detail/metaTitle/2fbb5fe2e29a4d70aa5854ce7ce3e20b/_/cce6cc404e7345c3a06adb6e0dca863d/base').then(() => {
            cy.log('» Click the create button');
            cy.get('.dreisc-seo-bulk-detail__create-action')
                .should('be.visible')
                .click();

            cy.log('» Select "Artikelname"');
            cy.get('.dreisc-seo-bulk-detail-base__field-dreiscSeoBulkTemplateId')
                .typeSingleSelect(null, { exactMatch: 'Artikelname' });

            cy.log('» Save');
            cy.get('.dreisc-seo-bulk-detail__save-action')
                .should('be.visible')
                .click();
        });
    });

    it('Set template of the "Mainshop" category', () => {
        cy.log('» Open the details');
        cy.visit('/admin#/dreisc/seo/bulk/product/detail/metaTitle/2fbb5fe2e29a4d70aa5854ce7ce3e20b/_/fdb2de5abc7a497499f2358ba9b2aa75/base').then(() => {
            cy.log('» Click the create button');
            cy.get('.dreisc-seo-bulk-detail__create-action')
                .should('be.visible')
                .click();

            cy.log('» Select "Artikelname"');
            cy.get('.dreisc-seo-bulk-detail-base__field-dreiscSeoBulkTemplateId')
                .typeSingleSelect(null, { exactMatch: 'Artikelname' });

            cy.log('» Save');
            cy.get('.dreisc-seo-bulk-detail__save-action')
                .should('be.visible')
                .click();
        });
    });

    it('Set template of the "Mainshop » Products" category', () => {
        cy.log('» Open the details');
        cy.visit('/admin#/dreisc/seo/bulk/product/detail/metaTitle/2fbb5fe2e29a4d70aa5854ce7ce3e20b/_/c542db9e9d964fe29a15061440a68730/base').then(() => {
            cy.log('» Click the create button');
            cy.get('.dreisc-seo-bulk-detail__create-action')
                .should('be.visible')
                .click();

            cy.log('» Select "Artikelname"');
            cy.get('.dreisc-seo-bulk-detail-base__field-dreiscSeoBulkTemplateId')
                .typeSingleSelect(null, { exactMatch: 'Artikelname' });

            cy.log('» Save');
            cy.get('.dreisc-seo-bulk-detail__save-action')
                .should('be.visible')
                .click();
        });
    });

    it('Set template of the "Mainshop » Products » Standard-Products" category', () => {
        cy.log('» Open the details');
        cy.visit('/admin#/dreisc/seo/bulk/product/detail/metaTitle/2fbb5fe2e29a4d70aa5854ce7ce3e20b/_/3fa301fbf6a043d6b90e45d6a28e89d7/base').then(() => {
            cy.log('» Click the create button');
            cy.get('.dreisc-seo-bulk-detail__create-action')
                .should('be.visible')
                .click();

            cy.log('» Select "Artikelname"');
            cy.get('.dreisc-seo-bulk-detail-base__field-dreiscSeoBulkTemplateId')
                .typeSingleSelect(null, { exactMatch: 'Artikelname' });

            cy.log('» Save');
            cy.get('.dreisc-seo-bulk-detail__save-action')
                .should('be.visible')
                .click();
        });
    });

    it('Set template of the "Mainshop » Products » Main-Products" category', () => {
        cy.log('» Open the details');
        cy.visit('/admin#/dreisc/seo/bulk/product/detail/metaTitle/2fbb5fe2e29a4d70aa5854ce7ce3e20b/_/e403cec98def4844affc68465632b15d/base').then(() => {
            cy.log('» Click the create button');
            cy.get('.dreisc-seo-bulk-detail__create-action')
                .should('be.visible')
                .click();

            cy.log('» Select "Artikelname"');
            cy.get('.dreisc-seo-bulk-detail-base__field-dreiscSeoBulkTemplateId')
                .typeSingleSelect(null, { exactMatch: 'Artikelname' });

            cy.log('» Save');
            cy.get('.dreisc-seo-bulk-detail__save-action')
                .should('be.visible')
                .click();
        });
    });

    it('Start deleting the "Artikelname" template', () => {
        cy.server();
        cy.route({
            url: '/api/v1/dreisc.seo/dreisc.seo.bulk/deleteBulkTemplate',
            method: 'post'
        }).as('bulkTemplatesDeleted');

        cy.route({
            url: '/api/v1/dreisc.seo/dreisc.seo.bulk/deleteBulkTemplate',
            method: 'post'
        }).as('deleteBulkTemplate');
        cy.log('» Open the details');
        cy.visit('/admin#/dreisc/seo/bulk/product/detail/metaTitle/2fbb5fe2e29a4d70aa5854ce7ce3e20b/_/e403cec98def4844affc68465632b15d/base').then(() => {
            cy.log('» Test, if the template was created and check the checkbox of the element');
                    cy.get('.dreisc-seo-bulk-template-list__grid')
                        .contains('Artikelname')
                        .parents('.sw-grid-row')
                        .find('.sw-field__checkbox input')
                        .check();
                });

                cy.log('» Click the delete button');
                cy.get('.dreisc-seo-bulk-template-list__delete-button')
                    .should('be.visible')
                    .click();

                cy.log('» Click the confirm button');
                cy.get('.dreisc-seo-bulk-detail__confirm-button')
                    .should('be.visible')
                    .click();

                cy.log('» Check modalbox content');
                cy.get('.dreisc-seo-bulk-detail__deleting_template_in_use_modal_confirm-delete-text')
                    .should('be.visible')
                    .contains('Das Template Artikelname ist bei 8 Kategorie(n) als Bulk Template hinterlegt');

                cy.get('.dreisc-seo-bulk-detail__deleting_template_in_use_modal_confirm-button')
                    .should('be.visible')
                    .click()

                cy.wait(1000);
    });

    it('Check if the seo bulk setting of the "Mainshop » Products » Main-Products" category is deleted', () => {
        cy.server();
        cy.route({
            url: '/api/v1/search/dreisc-seo-bulk-template',
            method: 'post'
        }).as('bulkTemplatesUpdated');

        cy.log('» Open the details');
        cy.visit('/admin#/dreisc/seo/bulk/product/detail/metaTitle/2fbb5fe2e29a4d70aa5854ce7ce3e20b/_/e403cec98def4844affc68465632b15d/base').then(() => {
            cy.get('.dreisc-seo-bulk-detail__create-action')
                .should('be.visible')
                .click();

            cy.log('» Wait for the update of the bulk templates');
                cy.wait('@bulkTemplatesUpdated').then(() => {
                    cy.log('» Test, if the template is not displays anymore');
                    cy.get('.dreisc-seo-bulk-template-list__grid')
                        .should('not.contain', 'Artikelname');
                });
        });
    });
});
