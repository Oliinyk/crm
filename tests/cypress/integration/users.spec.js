describe('Users', () => {

    before(() => {
        cy.refreshDatabase()
        cy.seed('PermissionSeeder')
        cy.create('App\\Models\\User', {email: 'johndoe@example.com'});
    })

    context('See / update user details', () => {

        it('Open User details popup', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/people/user')
            cy.get('tbody tr.border-b .user-name').click()
            cy.contains('Update User').should('exist')
        })

        it('Close User details popup ("cross" button)', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/people/user')
            cy.get('tbody tr.border-b .user-name').click()
            cy.contains('Update User')
            cy.get('.btn-close').click()
            cy.contains('Update User').should('not.exist')
        })

        it('Close User details popup ("cancel" button)', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/people/user')
            cy.get('tbody tr.border-b .user-name').click()
            cy.contains('Update User')
            cy.get('.btn-cancel').click()
            cy.contains('Update User').should('not.exist')
        })

        it('Close User details popup (click outside the popup)', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/people/user')
            cy.get('tbody tr.border-b .user-name').click()
            cy.contains('Update User').clickOutside()
            cy.contains('Update User').should('not.exist')
        })

    })

})
