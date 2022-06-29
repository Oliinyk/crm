describe('Ticket types page', () => {

    before(() => {
        cy.refreshDatabase()
        cy.seed('PermissionSeeder')
        cy.create('App\\Models\\User', {email: 'johndoe@example.com'});
    })

    context('Create Ticket type', () => {

        it('Open Create ticket type popup', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/templates/ticket-type')
            cy.contains('New ticket type').click()
            cy.contains('Create ticket type')
        })

        it('Close Create ticket type popup ("cross" button)', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/templates/ticket-type')
            cy.contains('New ticket type').click()
            cy.contains('Create ticket type')
            cy.get('.btn-close').click()
            cy.contains('Create ticket type').should('not.exist')
        })

        it('Close Create ticket type popup ("cancel" button)', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/templates/ticket-type')
            cy.contains('New ticket type').click()
            cy.contains('Create ticket type')
            cy.contains('Cancel').click()
            cy.contains('Create ticket type').should('not.exist')
        })

        it('Close Create ticket type popup (click outside the popup)', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/templates/ticket-type')
            cy.contains('New ticket type').click()
            cy.contains('Create ticket type').clickOutside()
            cy.contains('Create ticket type').should('not.exist')
        })

        it('Create ticket type Add default field', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/templates/ticket-type')
            cy.contains('New ticket type').click()
            cy.contains('Create ticket type')
            cy.get('#create-ticket-type-name').clear().type('Test ticket type')
            cy.contains('Add field').click()
            cy.contains('Due Date').click()
            cy.get('[type="submit"]').click()
            cy.contains('Success')
        })

        it('Create ticket type Open "Add field" popup', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/templates/ticket-type')
            cy.contains('New ticket type').click()
            cy.contains('Create ticket type')
            cy.get('#create-ticket-type-name').clear().type('Test ticket type')
            cy.contains('Add field').click()
        })

        it('Create ticket type Close "Add field" popup (click outside popup)', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/templates/ticket-type')
            cy.contains('New ticket type').click()
            cy.contains('Create ticket type')
            cy.get('#create-ticket-type-name').clear().type('Test ticket type')
            cy.contains('Add field').click()
            cy.contains('Short text').clickOutside()
            cy.contains('Short text').should('not.exist')
        })

        it('Try to add same default field as already added', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/templates/ticket-type')
            cy.contains('New ticket type').click()
            cy.contains('Create ticket type')
            cy.get('.vm--modal').find('input').should('have.length', 2)
            cy.get('#create-ticket-type-name').clear().type('Test ticket type')
            cy.contains('Add field').click()
            cy.contains('Due Date').click()
            cy.contains('Add field').click()
            cy.contains('Due Date').click()
            cy.get('.vm--modal').find('input').should('have.length', 3)
        })

        it('Create ticket type with Add custom field', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/templates/ticket-type')
            cy.contains('New ticket type').click()
            cy.contains('Create ticket type')
            cy.get('.vm--modal').find('input').should('have.length', 2)
            cy.get('#create-ticket-type-name').clear().type('Test ticket type0')
            cy.contains('Add field').click()
            cy.contains('Short text').click()
            cy.get('.vm--modal').find('input').should('have.length', 3)
            cy.get('.dndrop-container').find('[type="text"]').type('Short text title')
            cy.get('[type="submit"]').click()
            cy.contains('Success')
        })

        it('Create ticket type with Add separator with title', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/templates/ticket-type')
            cy.contains('New ticket type').click()
            cy.contains('Create ticket type')
            cy.get('.vm--modal').find('input').should('have.length', 2)
            cy.get('#create-ticket-type-name').clear().type('Test ticket type')
            cy.contains('Add field').click()
            cy.contains('Separator').click()
            cy.get('.vm--modal').find('input').should('have.length', 3)
            cy.get('.dndrop-draggable-wrapper').find('[type="text"]').type('Separator title')
            cy.get('[type="submit"]').click()
            cy.contains('Error')
        })

        it('Create ticket type with Add separator with title', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/templates/ticket-type')
            cy.contains('New ticket type').click()
            cy.contains('Create ticket type')
            cy.get('.vm--modal').find('input').should('have.length', 2)
            cy.get('#create-ticket-type-name').clear().type('Test2 ticket type')
            cy.contains('Add field').click()
            cy.contains('Separator').click()
            cy.get('.vm--modal').find('input').should('have.length', 3)
            cy.get('[type="submit"]').click()
            cy.contains('Success')
        })


        it('Create ticket type with all filled data', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/templates/ticket-type')
            cy.contains('New ticket type').click()
            cy.contains('Create ticket type')
            cy.get('#create-ticket-type-name').clear().type('Test3 ticket type')
            cy.contains('Add field').click()
            cy.contains('Short text').click()
            cy.get('.dndrop-container').find('[type="text"]').type('Short text title')
            cy.get('[type="submit"]').click()
            cy.contains('Success')
        })

        it('Create ticket type with all empty data', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/templates/ticket-type')
            cy.contains('New ticket type').click()
            cy.contains('Create ticket type')
            cy.get('[type="submit"]').click()
            cy.contains('The name field is required.')
        })

        it('Create ticket type with empty type', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/templates/ticket-type')
            cy.contains('New ticket type').click()
            cy.contains('Create ticket type')
            cy.get('#create-ticket-type-name').clear().type('Test4 ticket type')
            cy.get('[type="submit"]').click()
            cy.contains('The fields must have at least 1 items.')
        })

        it('Create ticket type with empty type name and field name', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/templates/ticket-type')
            cy.contains('New ticket type').click()
            cy.contains('Create ticket type')
            cy.get('#create-ticket-type-name').clear().type('Test5 ticket type')
            cy.contains('Add field').click()
            cy.contains('Short text').click()
            cy.get('.dndrop-container').find('[type="text"]')
            cy.get('[type="submit"]').click()
            cy.contains('The fields.0.name field is required unless')
        })

    })

    context('View / update / disable Ticket type', () => {

        it('Open Ticket type details popup', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/templates/ticket-type')
            cy.contains('New ticket type').click()
            cy.contains('Create ticket type')
            cy.get('.vm--modal').find('input').should('have.length', 2)
            cy.get('#create-ticket-type-name').clear().type('Test6 ticket type')
            cy.contains('Add field').click()
            cy.contains('Short text').click()
            cy.get('.vm--modal').find('input').should('have.length', 3)
            cy.get('.dndrop-container').find('[type="text"]').type('Short text title')
            cy.get('[type="submit"]').click()
            cy.contains('Test6 ticket type').click()
            cy.contains('Ticket type details')
        })

        it('Close Ticket type details popup ("cross" button)', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/templates/ticket-type')
            cy.contains('New ticket type').click()
            cy.contains('Create ticket type')
            cy.get('.vm--modal').find('input').should('have.length', 2)
            cy.get('#create-ticket-type-name').clear().type('Test7 ticket type')
            cy.contains('Add field').click()
            cy.contains('Short text').click()
            cy.get('.vm--modal').find('input').should('have.length', 3)
            cy.get('.dndrop-container').find('[type="text"]').type('Short text title')
            cy.get('[type="submit"]').click()
            cy.contains('Test7 ticket type').click()
            cy.contains('Ticket type details')
            cy.get('.btn-close').click()
            cy.contains('Ticket type details').should('not.exist')
        })

        it('Close Ticket type details popup ("cancel" button)', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/templates/ticket-type')
            cy.contains('New ticket type').click()
            cy.contains('Create ticket type')
            cy.get('.vm--modal').find('input').should('have.length', 2)
            cy.get('#create-ticket-type-name').clear().type('Test8 ticket type')
            cy.contains('Add field').click()
            cy.contains('Short text').click()
            cy.get('.vm--modal').find('input').should('have.length', 3)
            cy.get('.dndrop-container').find('[type="text"]').type('Short text title')
            cy.get('[type="submit"]').click()
            cy.contains('Test8 ticket type').click()
            cy.contains('Ticket type details')
            cy.contains('Cancel').click()
            cy.contains('Create ticket type').should('not.exist')
        })

        it('Close Ticket type details popup (click outside the popup)', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/templates/ticket-type')
            cy.contains('New ticket type').click()
            cy.contains('Create ticket type')
            cy.get('.vm--modal').find('input').should('have.length', 2)
            cy.get('#create-ticket-type-name').clear().type('Test9 ticket type')
            cy.contains('Add field').click()
            cy.contains('Short text').click()
            cy.get('.vm--modal').find('input').should('have.length', 3)
            cy.get('.dndrop-container').find('[type="text"]').type('Short text title')
            cy.get('[type="submit"]').click()
            cy.contains('Test9 ticket type').click()
            cy.contains('Ticket type details')
            cy.contains('Ticket type details').clickOutside()
            cy.contains('Ticket type details').should('not.exist')
        })

        it('Change ticket type details and update', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/templates/ticket-type')
            cy.contains('New ticket type').click()
            cy.contains('Create ticket type')
            cy.get('.vm--modal').find('input').should('have.length', 2)
            cy.get('#create-ticket-type-name').clear().type('Test10 ticket type')
            cy.contains('Add field').click()
            cy.contains('Short text').click()
            cy.get('.vm--modal').find('input').should('have.length', 3)
            cy.get('.dndrop-container').find('[type="text"]').type('Short text title')
            cy.get('[type="submit"]').click()
            cy.contains('Test10 ticket type').click()
            cy.contains('Ticket type details')
            cy.get('#ticket-type-udpate-mane').clear().type('New name ticket type')
            cy.get('.ttu-input-name').find('[type="text"]').clear().type('Short text title')
            cy.get('[type="submit"]').click()
        })

        it('Remove ticket type name and udpate', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/templates/ticket-type')
            cy.contains('New ticket type').click()
            cy.contains('Create ticket type')
            cy.get('.vm--modal').find('input').should('have.length', 2)
            cy.get('#create-ticket-type-name').clear().type('Test11 ticket type')
            cy.contains('Add field').click()
            cy.contains('Short text').click()
            cy.get('.vm--modal').find('input').should('have.length', 3)
            cy.get('.dndrop-container').find('[type="text"]').type('Short text title')
            cy.get('[type="submit"]').click()
            cy.contains('Test11 ticket type').click()
            cy.contains('Ticket type details')
            cy.get('#ticket-type-udpate-mane').clear()
            cy.get('.ttu-input-name').find('[type="text"]').clear().type('Short text title')
            cy.get('[type="submit"]').click()
            cy.contains('The name field is required.')
        })

    })

    context('Search Ticket types', () => {

        it('Search for existing Ticket type', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/templates/ticket-type')
            cy.get('[name="search"]').click().type('Test20')
            cy.contains('No ticket types found.')
            cy.get('[name="search"]').click().clear().type('Test11')
            cy.contains('Test11 ticket type').should('have.length', 1)
        })

    })

})
