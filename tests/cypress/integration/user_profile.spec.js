describe('user profile modal', () => {

    before(() => {
        cy.refreshDatabase()
        cy.seed('PermissionSeeder')
        cy.create('App\\Models\\User', {email: 'johndoe@example.com'});
    })

    context('basic modal logic', () => {

        it('Open My profile details popup', () => {
            cy.login({email: 'test@example.com'}).visit('/')
            cy.get('#user-profile-menu').click()
            cy.contains('My Profile').click()
            cy.contains('Update User').should('exist')
        })

        it('Close My profile details popup ("cross" button)', () => {
            cy.login({email: 'test@example.com'}).visit('/')
            cy.get('#user-profile-menu').click()
            cy.contains('My Profile').click()
            cy.get('.btn-close').click()
            cy.contains('Update User').should('not.exist')
        })

        it('Close My profile details popup ("cancel" button)', () => {
            cy.login({email: 'test@example.com'}).visit('/')
            cy.get('#user-profile-menu').click()
            cy.contains('My Profile').click()
            cy.get('.btn-cancel').click()
            cy.contains('Update User').should('not.exist')
        })

        it('Close My profile details popup (click outside the popup)', () => {
            cy.login({email: 'test@example.com'}).visit('/')
            cy.get('#user-profile-menu').click()
            cy.contains('My Profile').click()
            cy.contains('Update User').clickOutside()
            cy.contains('Update User').should('not.exist')
        })

    })

    context('with valid credentials', () => {

        it('Change First name and update', () => {
            cy.login({email: 'test@example.com'}).visit('/')
            cy.get('#user-profile-menu').click()
            cy.contains('My Profile').click()
            cy.get('#full-name').clear().type('test')
            cy.get('[type="submit"]').click()
            cy.contains('Success')
            cy.contains('User updated.')
            cy.get('#user-profile-menu').click()
            cy.contains('My Profile').click()
            cy.get('#full-name').clear().type('test')
        })

        it('Update with empty First name field', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/')
            cy.get('#user-profile-menu').click()
            cy.contains('My Profile').click()
            cy.get('#full-name').clear()
            cy.get('[type="submit"]').click()
            cy.contains('The full name field is required.')
        })

        it('Update with long name', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/')
            cy.get('#user-profile-menu').click()
            cy.contains('My Profile').click()
            cy.get('#full-name').clear().type('q'.repeat(51))
            cy.get('[type="submit"]').click()
            cy.contains('The full name must not be greater than 50 characters.')
        })

        it('Change email and update', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/')
            cy.get('#user-profile-menu').click()
            cy.contains('My Profile').click()
            cy.get('#full-email').clear().type('johndoe@example.com1')
            cy.get('[type="submit"]').click()
            cy.contains('User updated.')
        })

        it('Change email to wrong format and update', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/')
            cy.get('#user-profile-menu').click()
            cy.contains('My Profile').click()
            cy.get('#full-email').clear().type('johndoeTest')
            cy.get('[type="submit"]').click()
            cy.contains('The email must be a valid email address.')
        })

        it('Change password (filled correctly)', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/')
            cy.get('#user-profile-menu').click()
            cy.contains('My Profile').click()
            cy.get('#profile-password').clear().type('123456789')
            cy.get('[type="submit"]').click()
            cy.contains('User updated.')
        })

        it('Change password (filled correctly)', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/')
            cy.get('#user-profile-menu').click()
            cy.contains('My Profile').click()
            cy.get('#profile-password').clear().type('1')
            cy.get('[type="submit"]').click()
            cy.contains('The password must be at least 8 characters.')
        })

    })
})
