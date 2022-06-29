describe('top menu', () => {

    before(() => {
        cy.refreshDatabase()
        cy.seed('PermissionSeeder')
        cy.create('App\\Models\\User', {
            email: 'test@example.com',
            full_name:'Super Admin',
        });
        cy.seed('ProjectSeeder')

    })

    describe('dashboard tab', () => {

        it('must open dashboard page', () => {
            cy.login({email: 'test@example.com'}).visit('1/people/user')
            cy.contains('Dashboard').click()
                .assertRedirect('/1')
            cy.contains('Dashboard')
        })

    })

    describe('project tab', () => {

        context('project menu navigation', () => {

            it('must appear on click', () => {
                cy.login({email: 'test@example.com'}).visit('/')
                cy.contains('All projects').should('not.exist')
                cy.contains('Projects').click()
                cy.contains('All Projects').should('exist')
                cy.contains('Create Project').should('exist')
            })

            it('must show current projects', () => {
                cy.login({email: 'test@example.com'}).visit('/')
                cy.contains('Projects').click()
                cy.contains('Project - ').should('exist')
            })

            it('must show create projects tab', () => {
                cy.login({email: 'test@example.com'}).visit('/')
                cy.contains('Projects').click()
                cy.contains('Create Project').should('exist')
            })

            it('must show all projects tab', () => {
                cy.login({email: 'test@example.com'}).visit('/')
                cy.contains('Projects').click()
                cy.contains('All Projects').should('exist')
            })

            it('must disappear on click outside', () => {
                cy.login({email: 'test@example.com'}).visit('/')
                cy.contains('Projects').click()
                cy.contains('All Projects').should('exist')
                cy.contains('All Projects').clickOutside()
                cy.contains('All Projects').should('not.exist')
            })
        })

        context('project create modal', () => {

            it('must open create project modal', () => {
                cy.login({email: 'test@example.com'}).visit('/')
                cy.contains('Projects').click()
                cy.contains('Create Project').click()
                cy.get('button').contains('Create')
            })

        })

        context('project page', () => {

            it('must open project page', () => {
                cy.login({email: 'test@example.com'}).visit('/')
                cy.contains('Projects').click()
                cy.contains('Project -').click()
                cy.contains('Tickets')
            })

        })

        context('all projects page', () => {

            it('must open all projects tab', () => {
                cy.login({email: 'test@example.com'}).visit('/')
                cy.contains('Projects').click()
                cy.contains('All Project')
                    .click()
                    .assertRedirect('1/project')
            })

        })

    })

    describe('people tab', () => {

        context('people menu navigation', () => {

            it('must appear on click', () => {
                cy.login({email: 'test@example.com'}).visit('/')
                cy.contains('Users').should('not.exist')
                cy.contains('People').click()
                cy.contains('Users').should('exist')
            })

            it('must show clients tab', () => {
                cy.login({email: 'test@example.com'}).visit('/')
                cy.contains('Clients').should('not.exist')
                cy.contains('People').click()
                cy.contains('Clients').should('exist')
            })

            it('must show roles tab', () => {
                cy.login({email: 'test@example.com'}).visit('/')
                cy.contains('Roles').should('not.exist')
                cy.contains('People').click()
                cy.contains('Roles').should('exist')
            })

            it('must disappear on click outside', () => {
                cy.login({email: 'test@example.com'}).visit('/')
                cy.contains('People').click()
                cy.contains('Users').should('exist')
                cy.contains('Users').clickOutside()
                cy.contains('Users').should('not.exist')
            })

        })

        context('users page', () => {

            it('must open users page', () => {
                cy.login({email: 'test@example.com'}).visit('/')
                cy.contains('People').click()
                cy.contains('User')
                    .click()
                    .assertRedirect('1/people/user')
                cy.contains('Users')
            })

        })

        context('clients page', () => {

            it('must open clients page', () => {
                cy.login({email: 'test@example.com'}).visit('/')
                cy.contains('People').click()
                cy.contains('Clients')
                    .click()
                    .assertRedirect('1/people/client')
                cy.contains('Clients')
            })

        })

        context('roles page', () => {

            it('must open roles page', () => {
                cy.login({email: 'test@example.com'}).visit('/')
                cy.contains('People').click()
                cy.contains('Roles')
                    .click()
                    .assertRedirect('1/people/role')
                cy.contains('Roles')
            })

        })

    })

    describe('template tab', () => {

        context('template menu navigation', () => {

            it('must appear on click', () => {
                cy.login({email: 'test@example.com'}).visit('/')
                cy.contains('Reports').should('not.exist')
                cy.contains('Templates').click()
                cy.contains('Reports').should('exist')
            })

            it('must disappear on click outside', () => {
                cy.login({email: 'test@example.com'}).visit('/')
                cy.contains('Templates').click()
                cy.contains('Reports').should('exist')
                cy.contains('Reports').clickOutside()
                cy.contains('Reports').should('not.exist')
            })

        })

        context('ticket types page', () => {

            it('must open all ticket types page', () => {
                cy.login({email: 'test@example.com'}).visit('/')
                cy.contains('Templates').click()
                cy.contains('Ticket types')
                    .click()
                    .assertRedirect('1/templates/ticket-type')
            })

        })

        context('reports page', () => {

            it('reports')

        })

    })

    describe('user profile tab', () => {

        context('user profile menu navigation', () => {

            it('must appear on click', () => {
                cy.login({email: 'test@example.com'}).visit('/')
                cy.contains('My Profile').should('not.exist')
                cy.get('#user-profile-menu').click()
                cy.contains('My Profile').should('exist')
            })

            it('must disappear on click outside', () => {
                cy.login({email: 'test@example.com'}).visit('/')
                cy.get('#user-profile-menu').click()
                cy.contains('My Profile').should('exist')
                cy.contains('My Profile').clickOutside()
                cy.contains('My Profile').should('not.exist')
            })

        })

        context('my profile tab', () => {

            it('must open my profile modal', () => {
                cy.login({email: 'test@example.com'}).visit('/')
                cy.get('#user-profile-menu').click()
                cy.contains('My Profile').click()
                cy.contains('Update User').should('exist')
            })

        })

        context('manage users tab', () => {

            it('must open manage users page', () => {
                cy.login({email: 'test@example.com'}).visit('/')
                cy.get('#user-profile-menu').click()
                cy.contains('Manage Users').click()
                cy.assertRedirect('1/people/user')
            })

        })

        context('log out tab', () => {

            it('once a user logs out they cannot see the dashboard', () => {
                cy.login({email: 'test@example.com'}).visit('/')
                cy.get('#user-profile-menu').click()
                cy.contains('Log out').click()
                cy.assertRedirect('/login')
            })

        })

    })
})


