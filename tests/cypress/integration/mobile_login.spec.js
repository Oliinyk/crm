describe('login', () => {

    before(() => {
        cy.refreshDatabase()
        cy.seed('PermissionSeeder')
        cy.create('App\\Models\\User', {email: 'johndoe@example.com'});
    })

    context('iphone-se2 basic page logic', () => {

        beforeEach(() => {
            cy.viewport(375, 667)
        })

        it('must redirect to login page when the user is a guest', () => {
            cy.visit('/').assertRedirect('/login')
        })

        it('must redirect to the forgot password page', () => {
            cy.visit('/login')
                .contains('Forgot password?')
                .click()
            cy.url()
                .should('contain', '/forgot-password')
        })

    })

    context('with valid credentials', () => {

        beforeEach(() => {
            cy.viewport(375, 667)
        })

        it('works', () => {
            cy.visit('/login')
            cy.get('[type="submit"]').click()
            cy.contains('Dashboard')
        })

    })

    context('with invalid credentials', () => {

        beforeEach(() => {
            cy.viewport(375, 667)
        })

        it('too many attempts', () => {
            cy.artisan('cache:clear', {}, { log: false })
            cy.visit('/login')
            cy.get('input[type=email]').clear().type('johndoe@example.com')
            cy.get('input[type=password]').clear().type('123')
            cy.get('[type="submit"]').click()
            cy.get('.notification-icon-close').click()
            cy.get('[type="submit"]').click()
            cy.get('.notification-icon-close').click()
            cy.get('[type="submit"]').click()
            cy.get('.notification-icon-close').click()
            cy.get('[type="submit"]').click()
            cy.get('.notification-icon-close').click()
            cy.get('[type="submit"]').click()
            cy.get('.notification-icon-close').click()
            cy.get('[type="submit"]').click()
            cy.contains('Too many login attempts.')
            cy.artisan('cache:clear', {}, { log: false })
        })

        it('requires a valid email address', () => {
            cy.visit('/login')
            cy.get('input[type=email]').clear().type('test')
            cy.get('[type="submit"]').click()
            cy.get('input:invalid').should('have.length', 1)
        })

        it('requires an existing email address', () => {
            cy.visit('/login')
            cy.get('input[type=email]').type('test')
            cy.get('[type="submit"]').click()
            cy.contains('These credentials do not match our records.')
        })

        it('requires valid credentials', () => {
            cy.visit('/login')
            cy.get('input[type=email]').clear().type('johndoe@example.com')
            cy.get('input[type=password]').clear().type('123')
            cy.get('[type="submit"]').click()
            cy.contains('These credentials do not match our records.')
        })

    })


})
