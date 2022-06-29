<template>
  <div class="pt-2 pb-6 md:pr-6 border-t">
    <!-- if "parent and child tickets" fields are empty -->
    <div v-if="(ticket.parent_ticket === null && ticket.child_tickets.length === 0) || (ticket.parent_ticket === null && ticket.child_tickets.length)">
      <div class="flex justify-between mb-4">
        <p class="text-xs font-bold uppercase text-dark">{{ $t('Child tickets') }}</p>
        <button type="button" class="add-child-ticket" @click="addChildTicket()">
          <icon name="plus" class="w-6 h-6 fill-gray flex items-center cursor-pointer" />
        </button>
      </div>
      <div v-if="hasInput" class="mb-1.5">
        <text-input ref="childTicketInput"
                    v-model="childTicketName"
                    :placeholder="$t('Input child ticket title')"
                    @blur="saveChildTicket"
                    @keyup.native.enter="saveChildTicket"
        />
      </div>
      <div v-for="item in childTickets" :key="item.id" class="child-ticket">
        <div class="flex w-full bg-accent border border-accent rounded mb-1.5 items-end sm:items-center">
          <div class="flex items-center justify-between flex-1 py-1.5 pl-2 pr-0 cursor-pointer flex-wrap sm:flex-nowrap" @click="showChildModal(childTickets, item.id)">
            <div class="flex w-full sm:w-auto">
              <p class="text-gray mr-2">{{ item.id }}</p>
              <p>{{ item.title }}</p>
            </div>
            <div class="flex items-center mt-2 sm:mt-0 w-full sm:w-auto">
              <!-- Status -->
              <div v-if="item.status">
                <StatusText :status="item.status" class="text-sm py-0.5 mr-2" />
              </div>
              <!-- Assignee -->
              <user-icon
                v-if="item.assignee"
                size="small"
                :full-name="item.assignee.name"
                :src="item.assignee.image.url"
                :primary-bg="true"
              />
            </div>
          </div>
          <dropdown placement="bottom-end" class="flex items-center">
            <div class="pr-2 pb-2 sm:pb-0 child-ticket-drop-menu">
              <icon name="more" class="block w-6 h-6 fill-gray flex items-center"></icon>
            </div>
            <div slot="dropdown"
                 class="py-2 bg-white rounded shadow rounded-b-3xl rounded-t-none sm:rounded"
            >
              <div class="block px-6 py-2 hover:bg-light text-dark cursor-pointer" @click="deleteChildTicket(item.id)">
                {{ $t('Delete') }}
              </div>
            </div>
          </dropdown>
        </div>
      </div>
    </div>

    <!-- if “parent ticket“ is not empty -->
    <div v-if="ticket.parent_ticket !== null && ticket.child_tickets.length === 0">
      <div class="flex justify-between mb-4">
        <p class="text-xs font-bold uppercase text-dark">{{ $t('Parent tickets') }}</p>
      </div>
      <div class="flex w-full bg-accent border border-accent rounded mb-1.5 items-end sm:items-center">
        <div class="flex items-center justify-between flex-1 py-1.5 pl-2 pr-0 cursor-pointer flex-wrap sm:flex-nowrap" @click="showParentModal(ticket)">
          <div class="flex w-full sm:w-auto">
            <p class="text-gray mr-2">{{ ticket.parent_ticket.id }}</p>
            <p>{{ ticket.parent_ticket.title }}</p>
          </div>
          <div class="flex items-center mt-2 sm:mt-0 w-full sm:w-auto">
            <!-- Status -->
            <div v-if="ticket.parent_ticket.status">
              <StatusText :status="ticket.parent_ticket.status" class="text-sm py-0.5 mr-2" />
            </div>
            <!-- Assignee -->
            <user-icon
              v-if="ticket.parent_ticket.assignee"
              size="small"
              :full-name="ticket.parent_ticket.assignee.name"
              :src="ticket.parent_ticket.assignee.image.url"
              :primary-bg="true"
            />
          </div>
        </div>
        <dropdown placement="bottom-end" class="flex items-center">
          <div class="pr-2 pb-2 sm:pb-0">
            <icon name="more" class="block w-6 h-6 fill-gray flex items-center"></icon>
          </div>
          <div slot="dropdown"
               class="py-2 bg-white rounded shadow rounded-b-3xl rounded-t-none sm:rounded"
          >
            <div class="block px-6 py-2 hover:bg-light text-dark cursor-pointer">
              -----
            </div>
          </div>
        </dropdown>
      </div>
    </div>
  </div>
</template>

<script>
import StatusText from '@/Shared/Inputs/StatusText'

export default {
  name: 'ParentChildTicketInput',
  components: {
    StatusText,
  },
  props: {
    ticket: Object,
    childTickets: Array,
  },
  data() {
    return {
      hasInput: false,
      childTicketName: '',
    }
  },
  methods: {
    addChildTicket() {
      this.hasInput = true
      // add focus
      this.$nextTick(function() {
        this.$refs.childTicketInput.focus()
      })
    },
    showParentModal(ticket) {
      const url = this.route('ticket.show', {
        workspace: this.$page.props.auth.workspace_id,
        project: this.$page.props.project.id,
        ticket: ticket.parent_ticket.id,
      })
      const params = new URLSearchParams(window.location.search)
      this.$inertia.get(url, params)
    },
    showChildModal(ticket, id) {
      const url = this.route('ticket.show', {
        workspace: this.$page.props.auth.workspace_id,
        project: this.$page.props.project.id,
        ticket: id,
      })
      const params = new URLSearchParams(window.location.search)
      this.$inertia.get(url, params)
    },
    saveChildTicket() {
      if(this.childTicketName.length < 1) {
        this.hasInput = false
        return
      }
      const route = this.route('ticket.child.store', {
        workspace: this.$page.props.auth.workspace_id,
        project: this.$page.props.project.id,
        ticket: this.ticket.id,
      })
      this.$inertia.post(route, {
        'title': this.childTicketName,
        'ticket_type': this.ticket.ticket_type_id,
        'fields': this.ticket.fields.map((field) => {
          return {
            'type': field.type,
            'name': field.name,
            'value': field.type === this.$fieldTypes.TYPE_PARENT_TICKET ? this.ticket.id : null,
          }
        }),
      }, {
        onSuccess: () => {
          this.childTicketName = ''
          this.hasInput = false
        },
      })
    },
    deleteChildTicket(id) {
      if (confirm(this.$t('Are you sure you want to delete this ticket?'))) {
        const route = this.route('ticket.child.destroy', {
          workspace: this.$page.props.auth.workspace_id,
          project: this.$page.props.project.id,
          ticket: this.ticket.id,
          child: id,
        })
        this.$inertia.delete(route, {
          onSuccess: () => {
          },
        })
      }
    },
  },
}
</script>
