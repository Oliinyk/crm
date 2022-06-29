describe('registration', () => {

    before(() => {
        cy.refreshDatabase()
        cy.seed('PermissionSeeder')
        cy.create('App\\Models\\User', {email: 'johndoe@example.com'});
    })

    context('basic page logic', () => {

        it('must redirect to the sign in page', () => {
            cy.visit('/register')
                .contains('Sign in')
                .click()
            cy.assertRedirect('/login')
        })

    })

    context('with valid credentials', () => {

        it('works', () => {
            cy.visit('/register')
            cy.get('#full-name').clear().type('test')
            cy.get('#email').clear().type('test@example.com')
            cy.get('#password').clear().type('12345678')
            cy.get('#password-confirmation').clear().type('12345678')
            cy.get('[type="submit"]').click()
            cy.contains('Dashboard')
        })

    })

    context('with invalid credentials', () => {

        it('requires a valid email address', () => {
            cy.visit('/register')
            cy.get('#full-name').clear().type('test')
            cy.get('#email').clear().type('test')
            cy.get('#password').clear().type('12345678')
            cy.get('#password-confirmation').clear().type('12345678')
            cy.get('[type="submit"]').click()
            cy.get('input:invalid').should('have.length', 1)
        })

        it('requires a new email address', () => {
            cy.visit('/register')
            cy.get('#full-name').clear().type('test')
            cy.get('#email').clear().type('johndoe@example.com')
            cy.get('#password').clear().type('12345678')
            cy.get('#password-confirmation').clear().type('12345678')
            cy.get('[type="submit"]').click()
            cy.contains('The email has already been taken.')
        })

        it('requires valid credentials', () => {
            cy.visit('/register')
            cy.get('#full-name').clear()
            cy.get('#email').clear().type('123@example.com')
            cy.get('#password').clear().type('123')
            cy.get('#password-confirmation').clear().type('1234')
            cy.get('[type="submit"]').click()
            cy.contains('The full name field is required.')
            cy.contains('The password must be at least 8 characters.')
        })

        it('requires same password', () => {
            cy.visit('/register')
            cy.get('#full-name').clear().type('test')
            cy.get('#email').clear().type('test@example.com')
            cy.get('#password').clear().type('12345678')
            cy.get('#password-confirmation').clear().type('123')
            cy.get('[type="submit"]').click()
            cy.contains('The password confirmation does not match.')
        })

        it('requires max characters', () => {
            cy.visit('/register')
            cy.get('#full-name').clear().type('123456789012345678901234567890123456789012345678901')
            cy.get('#email').clear().type('123456789012345678901234567890123456789012345678901@example.com')
            cy.get('#password').clear().type('123456789012345678901234567890123456789012345678901')
            cy.get('#password-confirmation').clear().type('123456789012345678901234567890123456789012345678901')
            cy.get('[type="submit"]').click()
            cy.contains('The full name must not be greater than 50 characters.')
            cy.contains('The email must not be greater than 50 characters.')
            cy.contains('The password must not be greater than 50 characters.')
        })

        it('too many attempts', () => {
            cy.artisan('cache:clear', {}, {log: false})
            cy.visit('/register')
            cy.get('[type="submit"]').click()
            cy.get('[type="submit"]').click()
            cy.get('[type="submit"]').click()
            cy.get('[type="submit"]').click()
            cy.get('[type="submit"]').click()
            cy.get('[type="submit"]').click()
            cy.contains('Too many login attempts.')
            cy.artisan('cache:clear', {}, {log: false})
        })

    })

})
