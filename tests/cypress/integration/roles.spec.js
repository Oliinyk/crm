describe('roles page', () => {

    before(() => {
        cy.refreshDatabase()
        cy.seed('PermissionSeeder')
        cy.create('App\\Models\\User', {email: 'johndoe@example.com'});
    })

    context('Create Role', () => {

        it('Open Create role popup', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/people/role')
            cy.contains('Create Role').click()
            cy.get('#create-role-name')
        })

        it('Close Create role popup ("cross" button)', () => {
            cy.login({email: 'johndoe@example.com'}).visit('1/people/role')
            cy.get('.btn-cta-primary').click()
            cy.get('#create-role-name')
            cy.get('.btn-close').click()
            cy.contains('Workspace permissions').should('not.exist')
        })

        it('Close Create role popup ("cancel" button)', () => {
            cy.login({email: 'johndoe@example.com'}).visit('1/people/role')
            cy.contains('Create Role').click()
            cy.get('#create-role-name')
            cy.contains('Cancel').click()
            cy.contains('Workspace permissions').should('not.exist')
        })

        it('Close Create role popup (click outside the popup)', () => {
            cy.login({email: 'johndoe@example.com'}).visit('1/people/role')
            cy.contains('Create Role').click()
            cy.get('#create-role-name')
            cy.contains('Create Role').clickOutside()
            cy.contains('Workspace permissions').should('not.exist')
        })

    })

    context('Create Role', () => {

        it('Create role with filled Name and all empty data', () => {
            cy.login({email: 'johndoe@example.com'}).visit('1/people/role')
            cy.get('.btn-cta-primary').click()
            cy.get('#create-role-name').clear().type('test')
            cy.get('[type="submit"]').click()
            cy.contains('Success')
        })

        it('Create role with empty Name', () => {
            cy.login({email: 'johndoe@example.com'}).visit('1/people/role')
            cy.get('.btn-cta-primary').click()
            cy.get('#create-role-name').clear()
            cy.get('[type="submit"]').click()
            cy.contains('The name field is required.')
        })

    })

    context('View / update / delete Role', () => {

        it('Open Role details popup', () => {
            cy.login({email: 'johndoe@example.com'}).visit('1/people/role')
            cy.get('.btn-cta-primary').click()
            cy.get('#create-role-name').clear().type('testRole')
            cy.get('[type="submit"]').click()
            cy.contains('testRole').click()
            cy.contains('Update Role')
        })

        it('Close Role details popup ("cross" button)', () => {
            cy.login({email: 'johndoe@example.com'}).visit('1/people/role')
            cy.contains('testRole').click()
            cy.contains('Update Role')
            cy.get('.btn-close').click()
            cy.contains('Workspace permissions').should('not.exist')
        })

        it('Close Role details popup ("cancel" button)', () => {
            cy.login({email: 'johndoe@example.com'}).visit('1/people/role')
            cy.contains('testRole').click()
            cy.contains('Update Role')
            cy.contains('Cancel').click()
            cy.contains('Workspace permissions').should('not.exist')
        })

        it('Remove role name and udpate', () => {
            cy.login({email: 'johndoe@example.com'}).visit('1/people/role')
            cy.get('.btn-cta-primary').click()
            cy.get('#create-role-name').clear().type('testRole2')
            cy.get('[type="submit"]').click()
            cy.contains('Success')
            cy.contains('testRole2').click()
            cy.contains('Update Role')
            cy.get('#update-role-name').clear()
            cy.get('[type="submit"]').click()
            cy.contains('The name field is required.')
        })

        it('Close Role details popup (click outside the popup)', () => {
            cy.login({email: 'johndoe@example.com'}).visit('1/people/role')
            cy.contains('testRole').click()
            cy.contains('Update Role')
            cy.contains('Update Role').clickOutside()
            cy.contains('Workspace permissions').should('not.exist')
        })

        it('Change role details and update', () => {
            cy.login({email: 'johndoe@example.com'}).visit('1/people/role')
            cy.contains('testRole').click()
            cy.contains('Update Role')
            cy.get('#update-role-name').clear().type('RoleNew12')
            cy.get('[type="submit"]').click()
            cy.contains('Success')
        })

        it('Delete role that has no users', () => {
            cy.login({email: 'johndoe@example.com'}).visit('1/people/role')
            cy.contains('RoleNew12').click()
            cy.contains('Update Role')
            cy.get('.btn-outline-danger').click()
            cy.contains('Success')
        })

        it('Remove role name and delete role that has no users', () => {
            cy.login({email: 'johndoe@example.com'}).visit('1/people/role')
            cy.contains('test').click()
            cy.contains('Update Role')
            cy.get('#update-role-name').clear().type('testRoleRemove')
            cy.get('[type="submit"]').click()
            cy.contains('testRoleRemove').click()
            cy.contains('Update Role')
            cy.get('#update-role-name').clear()
            cy.get('[type="submit"]').click()
            cy.contains('The name field is required.')
            cy.get('.btn-outline-danger').click()
            cy.contains('Role deleted.')
        })

        it('Create Role details popup select', () => {
            cy.login({email: 'johndoe@example.com'}).visit('1/people/role')
            cy.contains('Create Role').click()
            cy.get('#create-role-name').type('New Role')
            cy.get('[data-cy=update-role-workspace-permissions-select]').click()
            cy.get('[data-cy=update-role-workspace-permissions-select]').contains('Only where participating').click()
            cy.get('[data-cy=update-role-delete-projects]').click()
            cy.get('[data-cy=update-role-delete-projects]').contains('Only created by user').click()
            cy.get('[data-cy=update-role-project-permissions]').click()
            cy.get('[data-cy=update-role-project-permissions]').contains('Only where participating').click()
            cy.get('[data-cy=update-role-edit-tickets]').click()
            cy.get('[data-cy=update-role-edit-tickets]').contains('Only where participating').click()
            cy.get('[data-cy=update-role-delete-tickets]').click()
            cy.get('[data-cy=update-role-delete-tickets]').contains('Only created by user').click()
            cy.get('[type="submit"]').click()
            cy.contains('Success')
            cy.contains('New Role').click()
            cy.get('[data-cy=update-role-workspace-permissions-select]').contains('Only where participating')
            cy.get('[data-cy=update-role-delete-projects]').contains('Only created by user')
            cy.get('[data-cy=update-role-project-permissions]').contains('Only where participating')
            cy.get('[data-cy=update-role-edit-tickets]').contains('Only where participating')
            cy.get('[data-cy=update-role-delete-tickets]').contains('Only created by user')
        })

        it('Create Role details popup checkbox', () => {
            cy.login({email: 'johndoe@example.com'}).visit('1/people/role')
            cy.contains('New Role').click()
            cy.contains('Update Role')
            cy.contains('Create projects').click()
            cy.contains('Edit Other User projects').click()
            cy.contains('Manage groups').click()
            cy.contains('See clients').click()
            cy.contains('Add and update clients').click()
            cy.contains('Delete clients').click()
            cy.contains('See roles').click()
            cy.contains('Add and update roles').click()
            cy.contains('Delete roles').click()
            cy.contains('Manage ticket types').click()
            cy.contains('Create tickets').click()
            cy.contains('Add/delete users in project').click()
            cy.get('[type="submit"]').click()
            cy.contains('Role updated')
            cy.contains('New Role').click()
            cy.get('.update-role-create-projects>div>svg').should('have.class', 'fill-secondary')
            cy.get('.update-role-edit-projects>div>svg').should('have.class', 'fill-secondary')
            cy.get('.update-role-manage-groups>div>svg').should('have.class', 'fill-secondary')
            cy.get('.update-role-see-clients>div>svg').should('have.class', 'fill-secondary')
            cy.get('.update-role-add-update-clients>div>svg').should('have.class', 'fill-secondary')
            cy.get('.update-role-delete-clients>div>svg').should('have.class', 'fill-secondary')
            cy.get('.update-role-delete-roles>div>svg').should('have.class', 'fill-secondary')
            cy.get('.update-role-manage-ticket-types>div>svg').should('have.class', 'fill-secondary')
            cy.get('.update-role-create-tickets>div>svg').should('have.class', 'fill-secondary')
            cy.get('.update-role-add-delete-users>div>svg').should('have.class', 'fill-secondary')
        })

        it('Update Role details popup select', () => {
            cy.login({email: 'johndoe@example.com'}).visit('1/people/role')
            cy.contains('testRole2').click()
            cy.contains('Update Role')
            cy.get('[data-cy=update-role-workspace-permissions-select]').click()
            cy.get('[data-cy=update-role-workspace-permissions-select]').contains('Only where participating').click()
            cy.get('[data-cy=update-role-delete-projects]').click()
            cy.get('[data-cy=update-role-delete-projects]').contains('Only created by user').click()
            cy.get('[data-cy=update-role-project-permissions]').click()
            cy.get('[data-cy=update-role-project-permissions]').contains('Only where participating').click()
            cy.get('[data-cy=update-role-edit-tickets]').click()
            cy.get('[data-cy=update-role-edit-tickets]').contains('Only where participating').click()
            cy.get('[data-cy=update-role-delete-tickets]').click()
            cy.get('[data-cy=update-role-delete-tickets]').contains('Only created by user').click()
            cy.get('[type="submit"]').click()
            cy.contains('Role updated')
            cy.contains('test').click()
            cy.get('[data-cy=update-role-workspace-permissions-select]').contains('Only where participating')
            cy.get('[data-cy=update-role-delete-projects]').contains('Only created by user')
            cy.get('[data-cy=update-role-project-permissions]').contains('Only where participating')
            cy.get('[data-cy=update-role-edit-tickets]').contains('Only where participating')
            cy.get('[data-cy=update-role-delete-tickets]').contains('Only created by user')
        })

        it('Update Role details popup checkbox', () => {
            cy.login({email: 'johndoe@example.com'}).visit('1/people/role')
            cy.contains('test').click()
            cy.contains('Update Role')
            cy.contains('Create projects').click()
            cy.contains('Edit Other User projects').click()
            cy.contains('Manage groups').click()
            cy.contains('See clients').click()
            cy.contains('Add and update clients').click()
            cy.contains('Delete clients').click()
            cy.contains('See roles').click()
            cy.contains('Add and update roles').click()
            cy.contains('Delete roles').click()
            cy.contains('Manage ticket types').click()
            cy.contains('Create tickets').click()
            cy.contains('Add/delete users in project').click()
            cy.get('[type="submit"]').click()
            cy.contains('Role updated')
            cy.contains('test').click()
            cy.get('.update-role-create-projects>div>svg').should('have.class', 'fill-secondary')
            cy.get('.update-role-edit-projects>div>svg').should('have.class', 'fill-secondary')
            cy.get('.update-role-manage-groups>div>svg').should('have.class', 'fill-secondary')
            cy.get('.update-role-see-clients>div>svg').should('have.class', 'fill-secondary')
            cy.get('.update-role-add-update-clients>div>svg').should('have.class', 'fill-secondary')
            cy.get('.update-role-delete-clients>div>svg').should('have.class', 'fill-secondary')
            cy.get('.update-role-delete-roles>div>svg').should('have.class', 'fill-secondary')
            cy.get('.update-role-manage-ticket-types>div>svg').should('have.class', 'fill-secondary')
            cy.get('.update-role-create-tickets>div>svg').should('have.class', 'fill-secondary')
            cy.get('.update-role-add-delete-users>div>svg').should('have.class', 'fill-secondary')
        })

    })



})
