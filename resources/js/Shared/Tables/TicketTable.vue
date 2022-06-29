<template>
  <div>
    <table-child-ticket-row :models="tickets"
                            :headers="headers"
                            :empty-message="$t('No ticket found.')"
                            :show-checkboxes="showCheckboxes"
                            @check="check"
    >
      <template v-if="visibleFields" #body="{model}">
        <td v-for="item in visibleFields" :key="item.id" class="cursor-pointer h-16 sm:h-auto p-0 sm:table-cell flex border-b sm:border-none"
            @click="showUpdateModal(model)"
        >
          <div class="flex items-center text-dark h-full"
               :class="model.parent_ticket ? 'p-0 sm:px-3 sm:py-4' : 'px-3 py-4'"
          >
            <div v-if="item.name === $fieldTypes.TYPE_STATUS"
                 class="sm:w-60"
                 :class="{'hidden sm:flex':model.parent_ticket}"
            >
              <StatusText :status="model.status" class="py-1" />
            </div>
            <div v-else-if="item.name === $fieldTypes.TYPE_PRIORITY"
                 class="sm:w-48"
                 :class="{'hidden sm:flex':model.parent_ticket}"
            >
              <PriorityText :priority="model[item.name]" />
            </div>
            <div v-else-if="item.name === $fieldTypes.TYPE_ASSIGNEE"
                 class="sm:w-60"
                 :class="{'hidden sm:flex':model.parent_ticket}"
            >
              <div v-if="model[item.name]" class="flex items-center">
                <user-icon
                  :full-name="model[item.name].name"
                  :src="model[item.name].image.url"
                />
                <p class="text-dark">{{ model[item.name].name }}</p>
              </div>
            </div>
            <div v-else-if="item.name === $fieldTypes.TYPE_WATCHERS"
                 class="sm:w-60 sm:-mb-1 flex sm:flex-wrap"
                 :class="{'hidden sm:flex':model.parent_ticket}"
            >
              <div v-for="watchers in model[item.name]" :key="watchers.id" class="flex items-center sm:mb-1 mr-2 sm:mr-0 whitespace-nowrap sm:whitespace-normal">
                <user-icon
                  :full-name="watchers.name"
                  :src="watchers.image.url"
                />
                <p class="text-dark">{{ watchers.name }}</p>
              </div>
            </div>
            <!-- child icon -->
            <div v-else-if="item.name === 'id' && model.parent_ticket" class="flex items-center">
              <span class="w-6 h-5">
                <icon name="child-tickets" class="block fill-gray-light flex items-center flex-none sm:ml-0 -ml-10" />
              </span>
              <p class="w-20 sm:pl-3 pl-8">{{ model[item.name] }}</p>
            </div>
            <!-- ID -->
            <div v-else-if="item.name === 'id'" class="w-20 truncate">
              {{ model[item.name] }}
            </div>
            <!-- Title -->
            <div v-else-if="item.name === 'title'" class="flex items-center sm:w-64">
              <p class="truncate">{{ model[item.name] }}</p>
              <icon v-if="model.child_tickets.length" name="parent-tickets" class="w-5 h-5 fill-gray-light hidden sm:flex items-center flex-none ml-3" />
            </div>
            <div v-else
                 class="sm:w-48"
                 :class="model.parent_ticket ? 'hidden sm:flex' : 'flex'"
            >
              {{ model[item.name] }}
            </div>
          </div>
        </td>
        <td class="absolute right-0 top-0 p-0 flex h-16 sm:h-full">
          <dropdown placement="bottom-end">
            <div class="flex items-center text-left sm:pr-4 py-4">
              <icon name="more" class="block w-6 h-6 fill-gray flex items-center"></icon>
            </div>
            <div slot="dropdown"
                 class="py-2 bg-white rounded shadow rounded-b-3xl rounded-t-none sm:rounded"
            >
              <div v-if="model.deleted_at == null"
                   class="block px-6 py-2 hover:bg-light text-dark cursor-pointer"
                   @click="deleteTicket(model.id)"
              >
                {{ $t('Delete') }}
              </div>
            </div>
          </dropdown>
        </td>
      </template>
    </table-child-ticket-row>
  </div>
</template>

<script>
import Layout from '@/Shared/Layouts/Layout'
import TableChildTicketRow from '@/Shared/Tables/TableChildTicketRow'
import StatusText from '@/Shared/Inputs/StatusText'
import PriorityText from '@/Shared/Inputs/PriorityText'

export default {
  components: {
    TableChildTicketRow,
    StatusText,
    PriorityText,
  },
  layout: Layout,
  props: {
    tickets: Object,
    showCheckboxes: Boolean,
    can: Object,
    fields: Array,
  },
  data() {
    return {
      selectedTickets: [],
    }
  },
  computed: {
    headers() {
      return this.fields.filter(item => item.show).map(item => this.$t(item.name))
    },
    visibleFields() {
      return this.fields.filter(item => item.show)
    },
  },
  methods: {
    check(checkedItems) {
      this.$emit('check', checkedItems)
    },
    showUpdateModal(ticket) {
      const url = this.route('ticket.show', {
        workspace: this.$page.props.auth.workspace_id,
        project: this.$page.props.project.id,
        ticket: ticket.id,
      })
      const params = new URLSearchParams(window.location.search)
      this.$inertia.get(url, params)
    },
    // delete ticket
    deleteTicket(id) {
      if (confirm(this.$t('Are you sure you want to delete this ticket?'))) {
        const route = this.route('ticket.destroy', {
          workspace: this.$page.props.auth.workspace_id,
          project: this.$page.props.project.id,
          ticket: id,
        })
        this.$inertia.delete(route)
      }
    },
  },
}
</script>

<style scoped>
@media (max-width: 639px) {
  .parent-item td:nth-child(1) {
    background-color: #F1F5F4;
  }
}
</style>
