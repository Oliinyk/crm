describe('Tickets page', () => {

    before(() => {
        cy.refreshDatabase()
        cy.seed('PermissionSeeder')
        cy.seed('TicketTestingSeeder')

    })

    context('Create ticket', () => {

        it('Open Create ticket popup', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/project/1/ticket')
            cy.contains('New ticket').click()
            cy.contains('Create ticket')
        })

        it('Close Create ticket popup ("cross" button)', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/project/1/ticket')
            cy.contains('New ticket').click()
            cy.contains('Create ticket')
            cy.get('#modals-container .btn-close').click()
            cy.contains('Create ticket').should('not.exist')
        })

        it('Close Create ticket popup ("cancel" button)', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/project/1/ticket')
            cy.contains('New ticket').click()
            cy.contains('Create ticket')
            cy.get('#modals-container').find('.btn-cancel').click()
            cy.contains('Create ticket').should('not.exist')
        })

        it('Close Create ticket popup (click outside the popup)', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/project/1/ticket')
            cy.contains('New ticket').click()
            cy.contains('Create ticket')
            cy.contains('Create ticket').clickOutside()
            cy.contains('Create ticket').should('not.exist')
        })

        it('Create ticket type with filled Title only', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/project/1/ticket')
            cy.contains('New ticket').click()
            cy.contains('Create ticket')
            cy.get('#modals-container .vs__selected-options').click()
            cy.contains('New Ticket type').click()
            //cy.get('.title-block').find('input').type('New Ticket name')
            cy.get('#modals-container [type="submit"]').click()
            cy.contains('The title field is required.')
        })

        it('Create ticket type with all filled data', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/project/1/ticket')
            cy.contains('New ticket').click()
            cy.contains('Create ticket')
            cy.get('#modals-container .vs__selected-options').click()
            cy.contains('New Ticket type').click()
            cy.get('.title-block').find('input').type('New Ticket name')
            cy.get('#modals-container [type="submit"]').click()
            cy.contains('Success')
        })

        it('Create ticket type with all empty data', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/project/1/ticket')
            cy.contains('New ticket').click()
            cy.contains('Create ticket')
            cy.get('#modals-container [type="submit"]').click()
            cy.contains('Error')
        })

    })

    context('View ticket', () => {

        it('Open Ticket details popup from the list view', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/project/1/ticket')
            cy.contains('Apple').click()
            cy.contains('Comments')
        })

        it('Close Ticket details popup ("cross" button)', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/project/1/ticket/1')
            cy.get('.vm--modal .btn-close').click()
            cy.contains('Comments').should('not.exist')
        })

        it('Close Ticket details popup (click outside the popup)', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/project/1/ticket/1')
            cy.get('.vm--modal').clickOutside()
            cy.contains('Comments').should('not.exist')
        })

    })

    context('Update ticket', () => {

        it('Update title', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/project/1/ticket/1')
            cy.get('.ticket-title').find('[type="text"]').clear().type('New name Ticket1')
            cy.get('.vm--modal').click()
            cy.contains('New ticket')
        })

        it('Update status', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/project/1/ticket/1')
            cy.contains('Comments')
            cy.get('[data-cy="Status"]').find('[type="search"]').click()
            cy.contains('In Progres').click()
            cy.contains('Comments').click()
            cy.get('.vm--modal').clickOutside()
            cy.contains('New ticket')
        })

        it('Update Priority', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/project/1/ticket/1')
            cy.contains('Comments')
            cy.get('[data-cy="Priority"]').find('[type="search"]').click()
            cy.contains('High').click()
            cy.contains('Comments').click()
            cy.get('.vm--modal').clickOutside()
            cy.contains('New ticket')
        })

        it('Update Layer', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/project/1/ticket/1')
            cy.contains('Comments')
            cy.get('[data-cy="Layer"]').find('[type="search"]').click()
            cy.contains('NewLayer').click()
            cy.contains('Comments').click()
            cy.get('.vm--modal').clickOutside()
            cy.contains('New ticket')
        })

        it('Update Assignee', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/project/1/ticket/1')
            cy.contains('Comments')
            cy.get('[data-cy="Assignee"]').find('[type="search"]').click()
            cy.contains('John Doe').click()
            cy.contains('Comments').click()
            cy.get('.vm--modal').clickOutside()
            cy.contains('New ticket')
        })

        it('Update Watchers', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/project/1/ticket/1')
            cy.contains('Comments')
            cy.get('[data-cy="Watchers"]').find('[type="search"]').click()
            cy.contains('John Doe').click()
            cy.contains('Comments').click()
            cy.get('.vm--modal').clickOutside()
            cy.contains('New ticket')
        })

        it('Update Start Date', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/project/1/ticket/1')
            cy.contains('Comments')
            cy.get('[data-cy="Start_Date"]').find('[type="text"]').click()
            cy.contains('10').click()
            cy.contains('Comments').click()
            cy.get('.vm--modal').clickOutside()
            cy.contains('New ticket')
        })

        it('Update Due Date', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/project/1/ticket/1')
            cy.contains('Comments')
            cy.get('[data-cy="Due_Date"]').find('[type="text"]').click()
            cy.contains('25').click()
            cy.contains('Comments').click()
            cy.get('.vm--modal').clickOutside()
            cy.contains('New ticket')
        })

        it('Update Progress', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/project/1/ticket/1')
            cy.contains('Comments')
            cy.get('[data-cy="Progress"]').find('[type="number"]').click().type('5')
            cy.contains('Comments').click()
            cy.get('.vm--modal').clickOutside()
            cy.contains('New ticket')
        })

        it('Update time estimate', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/project/1/ticket/1')
            cy.contains('Comments')
            cy.get('[data-cy="Progress"]').find('[type="number"]').click().type('5d')
            cy.contains('Comments').click()
            cy.get('.vm--modal').clickOutside()
            cy.contains('New ticket')
        })

        it('Update Comments', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/project/1/ticket/1')
            cy.contains('Comments')
            cy.get('#comment').click().type('Lorem Ipsum')
            cy.get('.vm--modal [type="submit"]').click()
            cy.contains('1 second ago')
        })

        it('Update Checklist', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/project/1/ticket/1')
            cy.contains('Ticket type')
            cy.contains('Checklist')
            cy.get('.add-child-ticket').click()
            cy.get('[placeholder="Input child ticket title"]').type('first child ticket')
            cy.contains('Checklist').click()
            cy.get('.vm--modal .btn-close').click()
            cy.contains('Checklist').should('not.exist')
            cy.contains('New ticket')
            cy.visit('/1/project/1/ticket/1')
            cy.contains('Ticket type')
            cy.contains('Checklist')
            cy.get('.child-ticket').should('have.length', 1)
        })

        it('Update Checklist', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/project/1/ticket/1')
            cy.contains('Checklist')
            cy.get('.add-child-ticket').click()
            cy.get('[placeholder="Input child ticket title"]').type('firstchildticketfirstchildticketfirstchildticketfirst')
            cy.contains('Checklist').click()
            cy.contains('Error')
        })

    })

    context('Delete ticket', () => {

        /*it('Delete ticket after open popup', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/project/1/ticket/1')
            cy.contains('Comments')
            cy.get('.vm--modal').find('[type="button"]').click({multiple: true})
            cy.contains('Delete').click()
            cy.contains('Comments').should('not.exist')
            cy.contains('Ticket deleted')
        })*/

        it('Delete ticket from the list view', () => {
            cy.login({email: 'johndoe@example.com'}).visit('/1/project/1/ticket/1')
            cy.get('.dropdown-actions').click()
            cy.contains('Delete').click()
            cy.contains('Ticket deleted')
        })

    })



})
