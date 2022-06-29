describe('project page', () => {

    before(() => {
        cy.refreshDatabase()
        cy.seed('PermissionSeeder')
        cy.create('App\\Models\\User', {email: 'johndoe@example.com'});
    })

    context('Create a Project', () => {

        it('Open Create project popup', () => {
            cy.login({email: 'johndoe@example.com'}).visit('1/project')
            cy.contains('Create Project').click()
            cy.get('[data-cy=create-project-name]')
        })

        it('Close Create project popup ("cross" button)', () => {
            cy.login({email: 'johndoe@example.com'}).visit('1/project')
            cy.contains('Create Project').click()
            cy.get('[data-cy=create-project-name]')
            cy.get('.btn-close').click()
            cy.contains('Client').should('not.exist')
        })

        it('Close Create project popup ("cancel" button)', () => {
            cy.login({email: 'johndoe@example.com'}).visit('1/project')
            cy.contains('Create Project').click()
            cy.get('[data-cy=create-project-name]')
            cy.contains('Cancel').click()
            cy.contains('Client').should('not.exist')
        })

        it('Close Create project popup (click outside the popup)', () => {
            cy.login({email: 'johndoe@example.com'}).visit('1/project')
            cy.contains('Create Project').click()
            cy.get('[data-cy=create-project-name]')
            cy.contains('Create Project').clickOutside()
            cy.contains('Client').should('not.exist')
        })

    })

    context('Actions with the project from table', () => {

        it('Open Project actions menu', () => {
            cy.login({email: 'johndoe@example.com'}).visit('1/project')
            cy.contains('Create Project').click()
            cy.get('[data-cy=create-project-name]').clear().type('a8')
            cy.get('[type="submit"]').click()
            cy.contains('Success')
            cy.get('[name="search"]').type('a8')
            cy.contains('a8')
            cy.get('.project-more-menu').click()
            cy.contains('Project Settings')
        })

        it('Close Project actions menu popup (click outside the popup)', () => {
            cy.login({email: 'johndoe@example.com'}).visit('1/project')
            cy.get('[name="search"]').clear().type('8')
            cy.contains('a8')
            cy.get('.project-more-menu').click()
            cy.contains('Project Settings').clickOutside()
            cy.contains('Project Settings').should('not.exist')
        })

        it('Delete from actions menu', () => {
            cy.login({email: 'johndoe@example.com'}).visit('1/project')
            cy.get('[name="search"]').clear().type('8')
            cy.contains('a8')
            cy.get('.project-more-menu').click()
            cy.contains('Move to trash').click()
            cy.contains('Project deleted.')
        })


    })

    context('Create a Project', () => {

        it('Create project with all filled data', () => {
            cy.login({email: 'johndoe@example.com'}).visit('1/project')
            cy.contains('Create Project').click()
            cy.get('[data-cy=create-project-name]').clear().type('test')
            cy.get('[type="submit"]').click()
            cy.contains('Success')
        })

        it('Create project with all empty data', () => {
            cy.login({email: 'johndoe@example.com'}).visit('1/project')
            cy.contains('Create Project').click()
            cy.get('[data-cy=create-project-name]')
            cy.get('[type="submit"]').click()
            cy.contains('The name field is required.')
        })

    })

    context('Search projects', () => {

        it('Search for existing Project', () => {
            cy.login({email: 'johndoe@example.com'}).visit('1/project')
            cy.contains('Create Project').click()
            cy.get('[data-cy=create-project-name]').clear().type('testProject')
            cy.get('[type="submit"]').click()
            cy.contains('Create Project').click()
            cy.get('[data-cy=create-project-name]').clear().type('22')
            cy.get('[type="submit"]').click()
            cy.contains('Create Project').click()
            cy.get('[data-cy=create-project-name]').clear().type('33')
            cy.get('[type="submit"]').click()
            cy.get('[name="search"]').clear().type('testProject')
            cy.contains('testProject')
        })

        it('Search for not existing Project', () => {
            cy.login({email: 'johndoe@example.com'}).visit('1/project')
            cy.contains('Create Project').click()
            cy.get('[data-cy=create-project-name]').clear().type('testProject')
            cy.get('[type="submit"]').click()
            cy.contains('Create Project').click()
            cy.get('[data-cy=create-project-name]').clear().type('22')
            cy.get('[type="submit"]').click()
            cy.contains('Create Project').click()
            cy.get('[data-cy=create-project-name]').clear().type('33')
            cy.get('[type="submit"]').click()
            cy.get('[name="search"]').clear().type('test45')
            cy.contains('No projects found.')
        })

    })

    context('Create a Project', () => {

        it('Create project with Name', () => {
            cy.login({email: 'johndoe@example.com'}).visit('1/project')
            cy.contains('Create Project').click()
            cy.get('[data-cy=create-project-name]').clear().type('test')
            cy.get('[type="submit"]').click()
            cy.contains('Success')
        })
        it('Create project with Client', () => {
            cy.login({email: 'johndoe@example.com'}).visit('1/project')
            cy.contains('Create Project').click()
            cy.get('[data-cy=create-project-client]').clear().type('test')
            cy.get('[type="submit"]').click()
            cy.contains('Success')
        })
        it('Create project with Lead', () => {
            cy.login({email: 'johndoe@example.com'}).visit('1/project')
            cy.contains('Create Project').click()
            cy.get('[data-cy=create-project-lead]').clear().type('Lead')
            cy.get('[type="submit"]').click()
            cy.contains('Success')
        })
        it('Create project with Description', () => {
            cy.login({email: 'johndoe@example.com'}).visit('1/project')
            cy.contains('Create Project').click()
            cy.get('[data-cy=create-project-description]').clear().type('Description')
            cy.get('[type="submit"]').click()
            cy.contains('Success')
        })
        it('Create project with Address', () => {
            cy.login({email: 'johndoe@example.com'}).visit('1/project')
            cy.contains('Create Project').click()
            cy.get('[data-cy=create-project-address]').clear().type('Address')
            cy.get('[type="submit"]').click()
            cy.contains('Success')
        })
        it('Create project with Cost', () => {
            cy.login({email: 'johndoe@example.com'}).visit('1/project')
            cy.contains('Create Project').click()
            cy.get('[data-cy=create-project-cost]').clear().type('Cost')
            cy.get('[type="submit"]').click()
            cy.contains('Success')
        })
        it('Create project with Working Hours', () => {
            cy.login({email: 'johndoe@example.com'}).visit('1/project')
            cy.contains('Create Project').click()
            cy.get('[data-cy=create-project-working-hours]').clear().type('8')
            cy.get('[type="submit"]').click()
            cy.contains('The name field is required.')
            cy.get('[data-cy=create-project-name]').clear().type('Name 2')
            cy.get('[type="submit"]').click()
            cy.contains('Success')
        })


    })



})
