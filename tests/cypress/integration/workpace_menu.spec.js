describe('workspace menu', () => {

    before(() => {
        cy.refreshDatabase().seed()
    })

    context('workspace menu navigation', () => {

        it('Open Workspace details popup', () => {
            cy.login().visit('/')
            cy.contains('Workspace settings').should('not.exist')
            cy.contains('Personal').click()
            cy.contains('Workspace settings').should('exist')
        })

        it('Close Workspace details popup (click outside the popup)', () => {
            cy.login().visit('/')
            cy.contains('Personal').click()
            cy.contains('Workspace settings').should('exist')
            cy.contains('Workspace settings').clickOutside()
            cy.contains('Workspace settings').should('not.exist')
        })

        it('must show current workspace', () => {
            cy.login().visit('/').contains('Personal')
        })

        it('Open Workspace details popup', () => {
            cy.login()
                .visit('/')
                .contains('Personal')
                .click()
            cy.contains('Workspace settings')
        })

        it('must show create tab', () => {
            cy.login()
                .visit('/')
                .contains('Personal')
                .click()
            cy.contains('Create')
        })
    })

    context('workspace update modal', () => {

        it('must show workspace update modal', () => {
            cy.login().visit('/')
            .contains('Personal').click()
            cy.contains('Workspace settings').click()
            cy.contains('Update Workspace')
        })
    })


    context('Open workspace details, edit and delete it', () => {

        it('Close Workspace details popup ("cross" button)', () => {
            cy.login().visit('/')
            cy.contains('Personal').click()
            cy.contains('Workspace settings').click()
            cy.contains('Update Workspace')
            cy.get('.btn-close').click()
            cy.contains('Update Workspace').should('not.exist')
        })

        it('Close Workspace details popup ("cancel" button)', () => {
            cy.login().visit('/')
            cy.contains('Personal').click()
            cy.contains('Workspace settings').click()
            cy.contains('Update Workspace')
            cy.get('#modals-container').find('.btn-cancel').click()
            cy.contains('Update Workspace').should('not.exist')
        })

        it('Close Workspace details popup (click outside the popup)', () => {
            cy.login().visit('/')
            cy.contains('Personal').click()
            cy.contains('Workspace settings').click()
            cy.contains('Update Workspace').clickOutside()
            cy.contains('Update Workspace').should('not.exist')
        })

        it('Change Workspace name and update', () => {
            cy.login().visit('/')
            cy.contains('Personal').click()
            cy.contains('Workspace settings').click()
            cy.get('.pb-7').find('[type="text"]').clear().type('New name')
            cy.get('[type="submit"]').click()
            cy.contains('Update Workspace').should('not.exist')
            cy.contains('Success')
        })

        it('Delete name and press "Update"', () => {
            cy.login().visit('/')
            cy.contains('Personal').click()
            cy.contains('Workspace settings').click()
            cy.get('.pb-7').find('[type="text"]').clear()
            cy.get('[type="submit"]').click()
            cy.contains('Error')
        })

        it('Delete Workspace', () => {
            cy.login().visit('/')
            cy.contains('Personal').click()
            cy.contains('Workspace settings').click()
            cy.get('.btn-outline-danger').click()
            cy.contains('The workspace count must be at least 2.')
        })

    })

    context('Create workspace', () => {

       it('Open create Workspace popup', () => {
            cy.login()
                .visit('/')
                .contains('Personal')
                .click()
            cy.contains('Create').click()
            cy.contains('Create Workspace')
        })

        it('Close create Workspace popup ("cross" button)', () => {
            cy.login().visit('/')
            cy.contains('Personal').click()
            cy.contains('Create').click()
            cy.contains('Create Workspace')
            cy.get('.btn-close').click()
            cy.contains('Update Workspace').should('not.exist')
        })

        it('Close create Workspace popup ("cancel" button)', () => {
            cy.login().visit('/')
            cy.contains('Personal').click()
            cy.contains('Create').click()
            cy.contains('Create Workspace')
            cy.contains('Cancel').click()
            cy.contains('Update Workspace').should('not.exist')
        })

        it('Close create Workspace popup (click outside the popup)', () => {
            cy.login().visit('/')
            cy.contains('Personal').click()
            cy.contains('Create').click()
            cy.contains('Create Workspace').clickOutside()
            cy.contains('Update Workspace').should('not.exist')
        })

        it('Create Workspace with all filled details', () => {
            cy.login().visit('/')
            cy.contains('Personal').click()
            cy.contains('Create').click()
            cy.get('.pb-7').find('[type="text"]').clear().type('New name')
            cy.get('.vs__selected-options').find('[type="search"]').click()
            cy.get('.vs__dropdown-menu')
            cy.contains('Team').click()
            cy.get('[type="submit"]').click()
            cy.contains('Success')
        })

        it('Create Workspace with empty name', () => {
            cy.login().visit('/')
            cy.contains('Personal').click()
            cy.contains('Create').click()
            cy.get('.vs__selected-options').find('[type="search"]').click()
            cy.get('.vs__dropdown-menu')
            cy.contains('Team').click()
            cy.get('[type="submit"]').click()
            cy.contains('The name field is required.')
        })

        it('Create Workspace with empty plan', () => {
            cy.login().visit('/')
            cy.contains('Personal').click()
            cy.contains('Create').click()
            cy.get('.pb-7').find('[type="text"]').clear().type('New name')
            cy.get('[type="submit"]').click()
            cy.contains('The plan field is required.')
        })

        it('Create Workspace and Delete Workspace', () => {
            cy.login().visit('/')
            cy.contains('Personal').click()
            cy.contains('Create').click()
            cy.get('.pb-7').find('[type="text"]').clear().type('New Workspace 1')
            cy.get('.vs__selected-options').find('[type="search"]').click()
            cy.get('.vs__dropdown-menu')
            cy.contains('Team').click()
            cy.get('[type="submit"]').click()
            cy.contains('Success')
            cy.contains('New').click()
            cy.contains('Create').click()
            cy.get('.pb-7').find('[type="text"]').clear().type('New Workspace 2')
            cy.get('.vs__selected-options').find('[type="search"]').click()
            cy.get('.vs__dropdown-menu')
            cy.contains('Team').click()
            cy.get('[type="submit"]').click()
            cy.contains('Success')
            cy.contains('New').click()
            cy.contains('New Workspace 2').click()
            cy.contains('New').click()
            cy.contains('Workspace settings').click()
            cy.get('.btn-outline-danger').click()
            cy.contains('Workspace deleted.')
        })

    })


})


