describe('client page', () => {

    before(() => {
        cy.refreshDatabase()
        cy.seed('PermissionSeeder')
        cy.create('App\\Models\\User', {email: 'johndoe@example.com'});
    })

    context('Create client', () => {

        it('Open Create client popup', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/people/client')
            cy.contains('Create Client').click()
            cy.contains('Name')
        })

        it('Close Create client popup ("cross" button)', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/people/client')
            cy.contains('Create Client').click()
            cy.get('.btn-close').click()
            cy.contains('Name').should('not.exist')
        })

        it('Close Create client popup ("cancel" button)', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/people/client')
            cy.contains('Create Client').click()
            cy.contains('Cancel').click()
            cy.contains('Name').should('not.exist')
        })

        it('Close Create client popup (click outside the popup)', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/people/client')
            cy.contains('Create Client').click()
            cy.contains('Create Client').clickOutside()
            cy.contains('Name').should('not.exist')
        })

    })

    context('Create client', () => {

        it('Create client with all filled data', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/people/client')
            cy.contains('Create Client').click()
            cy.get('#client-name').clear().type('test')
            cy.get('#client-status').clear().type('test')
            cy.get('#client-email').clear().type('test@test.com')
            cy.get('#client-phone').clear().type('test')
            cy.get('#client-city').clear().type('test')
            cy.get('[type="submit"]').click()
            cy.contains('Success')
        })

        it('Create client with all empty data', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/people/client')
            cy.contains('Create Client').click()
            cy.get('#client-name').clear()
            cy.get('#client-status').clear()
            cy.get('#client-email').clear()
            cy.get('#client-phone').clear()
            cy.get('#client-city').clear()
            cy.get('[type="submit"]').click()
            cy.contains('The name field is required.')
        })

        it('Create client with empty Name, and filled next fields', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/people/client')
            cy.contains('Create Client').click()
            cy.get('#client-name').clear()
            cy.get('#client-status').clear().type('111')
            cy.get('#client-email').clear().type('test@test.com')
            cy.get('#client-phone').clear().type('111')
            cy.get('#client-city').clear().type('111')
            cy.get('[type="submit"]').click()
            cy.contains('The name field is required.')
        })

        it.skip('check email field is correct', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/people/client')
            cy.contains('Create Client').click()
            cy.get('#client-name').clear().type('test')
            cy.get('#client-email').clear().type('test@test.com')
            cy.get('[type="submit"]').click()
            cy.contains('Success')
        })

        it('with invalid e-mail', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/people/client')
            cy.contains('Create Client').click()
            cy.get('#client-name').clear().type('test')
            cy.get('#client-status').clear()
            cy.get('#client-email').clear().type('test')
            cy.get('#client-phone').clear()
            cy.get('#client-city').clear()
            cy.get('[type="submit"]').click()
            cy.contains('The email must be a valid email address.')
        })

        it('to long name', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/people/client')
            cy.contains('Create Client').click()
            cy.get('#client-name').clear().type('q'.repeat(51))
            cy.get('[type="submit"]').click()
            cy.contains('The name must not be greater than 50 characters.')
        })

    })

    context('Search clients', () => {

        it('Search for existing Client', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/people/client')
            cy.contains('Create Client').click()
            cy.get('#client-name').clear().type('test1')
            cy.get('[type="submit"]').click()
            cy.contains('Create Client').click()
            cy.get('#client-name').clear().type('test2')
            cy.get('[type="submit"]').click()
            cy.contains('Create Client').click()
            cy.get('#client-name').clear().type('test3')
            cy.get('[type="submit"]').click()
            cy.contains('Success')
            cy.get('[name="search"]').clear().type('test1')
            cy.contains('test1')
        })

        it('Search for not existing Client', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/people/client')
            cy.contains('Create Client').click()
            cy.get('#client-name').clear().type('test1')
            cy.get('[type="submit"]').click()
            cy.contains('Create Client').click()
            cy.get('#client-name').clear().type('test2')
            cy.get('[type="submit"]').click()
            cy.contains('Create Client').click()
            cy.get('#client-name').clear().type('test3')
            cy.get('[type="submit"]').click()
            cy.contains('Success')
            cy.get('[name="search"]').clear().type('test45')
            cy.contains('No users found.')
        })

    })

    context('View / update / delete Client', () => {

        it('Open Client details popup', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/people/client')
            cy.contains('Create Client').click()
            cy.get('#client-name').clear().type('Jeramy Schuster MD')
            cy.get('[type="submit"]').click()
            cy.contains('Jeramy Schuster MD').click()
            cy.contains('Update Client')
        })

        it('Close Client details popup ("cross" button)', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/people/client')
            cy.contains('Create Client').click()
            cy.get('#client-name').clear().type('Jeramy Schuster MD')
            cy.get('[type="submit"]').click()
            cy.contains('Jeramy Schuster MD').click()
            cy.contains('Update Client')
            cy.get('.btn-close').click()
        })

        it('Close Client details popup ("cancel" button)', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/people/client')
            cy.contains('Create Client').click()
            cy.get('#client-name').clear().type('Jeramy Schuster MD')
            cy.get('[type="submit"]').click()
            cy.contains('Jeramy Schuster MD').click()
            cy.contains('Update Client')
            cy.contains('Cancel').click()
        })

        it('Change all client details and update', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/people/client')
            cy.contains('Create Client').click()
            cy.get('#client-name').clear().type('Jeramy Schuster MD')
            cy.get('[type="submit"]').click()
            cy.contains('Jeramy Schuster MD').click()
            cy.contains('Update Client')
            cy.get('#name-update-client').clear().type('testName')
            cy.get('#status-update-client').clear().type('testStatus')
            cy.get('#email-update-client').clear().type('test1111@test.com')
            cy.get('#phone-update-client').clear().type('000 000 000')
            cy.get('#city-update-client').clear().type('testCity')
            cy.get('[type="submit"]').click()
            cy.contains('Success')
        })

        it('Remove client name and udpate', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/people/client')
            cy.contains('Create Client').click()
            cy.get('#client-name').clear().type('Jeramy Schuster MD')
            cy.get('#client-email').type('test6666@test.com')
            cy.get('[type="submit"]').click()
            cy.contains('Jeramy Schuster MD').click()
            cy.contains('Update Client')
            cy.get('#name-update-client').clear()
            cy.get('[type="submit"]').click()
            cy.contains('Error')
        })


    })

})
