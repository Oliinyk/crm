<template>
  <div
    class="pt-12 pb-32 px-6 sm:px-9 sm:pb-14 sm:h-full sm:overflow-auto h-screen overflow-y-scroll sm:max-h-screen-90"
  >
    <div class="flex justify-between items-start w-full pb-5">
      <div class="w-full md:w-3/5">
        <div class="flex mb-1">
          <p v-if="ticket.parent_ticket !== null" class="text-accent-dark mr-1">
            <span class="cursor-pointer hover:text-secondary" @click="showParentModal(ticket)">{{ ticket.parent_ticket.id }}</span> /
          </p>
          <p class="text-secondary">{{ ticket.id }}</p>
        </div>
        <!-- Title -->
        <div class="flex items-center -ml-2">
          <text-input :value="ticket.title"
                      class="w-full ticket-title"
                      :editable="true"
                      :title-input="true"
                      @input="saveTitle"
                      @blur="saveTicketTitle()"
          />
        </div>
      </div>
      <div class="flex">
        <div v-if="ticket.child_tickets.length" class="flex items-center py-2 px-3">
          <icon name="parent-tickets" class="block w-5 h-5 fill-gray-light flex items-center"></icon>
        </div>
        <dropdown placement="bottom-end">
          <div class="btn-icon dropdown-actions">
            <icon name="more" class="block w-6 h-6 fill-gray flex items-center"></icon>
          </div>
          <div slot="dropdown"
               class="py-2 bg-white rounded shadow rounded-b-3xl rounded-t-none sm:rounded"
          >
            <div class="flex items-center px-5 py-2 hover:bg-light text-dark text-sm cursor-pointer">
              <icon name="export" class="h-4 w-4 sm:mr-2 fill-gray"></icon>
              <span>{{ $t('Export') }}</span>
            </div>
            <div class="flex items-center px-5 py-2 hover:bg-light text-dark text-sm cursor-pointer">
              <icon name="share" class="w-4 sm:mr-2 fill-gray"></icon>
              <span>{{ $t('Share') }}</span>
            </div>
            <div class="flex items-center px-5 py-2 hover:bg-light text-dark text-sm cursor-pointer"
                 @click="destroy"
            >
              <icon name="trash" class="h-4 w-4 sm:mr-2 fill-gray"></icon>
              <span>{{ $t('Delete') }}</span>
            </div>
          </div>
        </dropdown>
        <button class="btn-icon btn-close" @click="$emit('close')">
          <icon name="close" class="w-6 h-6 fill-secondary"></icon>
        </button>
      </div>
    </div>
    <div class="border-t flex flex-wrap w-full">
      <div class="border-b md:border-b-0 md:border-r w-full md:w-3/5">
        <div class="pt-6 pb-4">
          <!-- Ticket type -->
          <div class="flex items-center mb-2 md:pr-6">
            <p class="text-gray font-bold min-w-100">{{ $t('Ticket type') }}</p>
            <div v-if="ticket.ticket_type" class="flex items-center pl-4">
              <div class="flex items-center">
                <p>{{ ticket.ticket_type }}</p>
              </div>
            </div>
            <p v-else class="text-gray font-light italic pl-4">{{ $t('None') }}</p>
          </div>
          <!-- Reporter -->
          <div class="flex items-center mb-2 md:pr-6">
            <p class="text-gray font-bold min-w-100">{{ $t('Reporter') }}</p>
            <div v-if="ticket.author" class="flex items-center pl-4">
              <user-icon
                size="small"
                :full-name="ticket.author.full_name"
                :src="ticket.author.image.url"
              />
              <p class="text-dark">{{ ticket.author.full_name }}</p>
            </div>
            <p v-else class="text-gray font-light italic pl-4">{{ $t('None') }}</p>
          </div>
          <div v-for="field in ticket.fields" :key="field.id" class="mb-2">
            <!-- Template Components -->
            <div v-if="[
              $fieldTypes.TYPE_STATUS,
              $fieldTypes.TYPE_PRIORITY,
              $fieldTypes.TYPE_LAYER,
              $fieldTypes.TYPE_ASSIGNEE,
              $fieldTypes.TYPE_WATCHERS,
              $fieldTypes.TYPE_ESTIMATE,
              $fieldTypes.TYPE_TIME_SPENT,
              $fieldTypes.TYPE_PROGRESS,
              $fieldTypes.TYPE_SHORT_TEXT,
              $fieldTypes.TYPE_DATE,
              $fieldTypes.TYPE_START_DATE,
              $fieldTypes.TYPE_DUE_DATE,
              $fieldTypes.TYPE_TIME,
              $fieldTypes.TYPE_NUMERAL,
              $fieldTypes.TYPE_DECIMAL,
              $fieldTypes.TYPE_LONG_TEXT,
              $fieldTypes.TYPE_SEPARATOR,
            ].includes(field.type)"
            >
              <component :is="field.type+'-input'"
                         v-model="field.value"
                         class="flex items-center md:pr-6"
                         :placeholder="$t('None')"
                         :editable="true"
                         :autosize="true"
                         :buttons-control="true"
                         :is-only-text="true"
                         :name="field.name"
                         :data-cy="field.name"
                         :id-field="field.id"
                         :ticket="ticket"
                         @input="saveTicket(field.id, field.value)"
                         @saveEstimateInput="saveEstimateInput"
              />
            </div>
          </div>
        </div>
        <!--Checklist-->
        <div class="pt-4 pb-6 md:pr-6 border-t">
          <div class="flex justify-between">
            <p class="font-bold text-dark">{{ $t('Checklist') }}</p>
            <button class="font-semibold text-dark">{{ $t('Hide checked items') }}</button>
          </div>
        </div>
        <!-- Parent/Child ticket -->
        <div v-if="$fieldTypes.TYPE_PARENT_TICKET">
          <parent-child-ticket-input :ticket="ticket"
                                     :child-tickets="ticket.child_tickets"
                                     @deleteChildTicket="deleteChildTicket"
          />
        </div>
        <!--Files-->
        <div class="pt-2 pb-4 md:pr-6 border-t">
          <p class="text-xs font-bold uppercase text-dark mb-5">{{ $t('Files') }}</p>
        </div>
        <!--Attachments-->
        <div class="pt-2 md:pr-6 border-t">
          <multiple-file-input v-model="media"
                               class="w-full"
                               type="file"
                               :is-full-screen="true"
                               @input="saveTicketImage"
          />
        </div>
      </div>
      <div class="w-full md:w-2/5">
        <div class="py-6 md:px-8">
          <div class="mb-6">
            <a class="cursor-pointer mr-8"
               :class="[ activeTab === 'comments' ? 'text-dark font-bold border-b-3 border-secondary' : 'text-gray' ]"
               @click="activeTab='comments'"
            >Comments</a>
            <a class="cursor-pointer"
               :class="[ activeTab === 'activityLog' ? 'text-dark font-bold border-b-3 border-secondary' : 'text-gray' ]"
               @click="activeTab='activityLog'"
            >Activity log</a>
          </div>
          <div class="text-dark">
            <div v-show="activeTab ==='comments'">
              <comments :ticket="ticket" />
            </div>
            <div v-show="activeTab ==='activityLog'">
              <activity-log :ticket="ticket" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import StatusInput from '@/Shared/Inputs/TicketFields/StatusInput'
import PriorityInput from '@/Shared/Inputs/TicketFields/PriorityInput'
import LayerInput from '@/Shared/Inputs/TicketFields/LayerInput'
import AssigneeInput from '@/Shared/Inputs/TicketFields/AssigneeInput'
import WatchersInput from '@/Shared/Inputs/TicketFields/WatchersInput'
import EstimateInput from '@/Shared/Inputs/TicketFields/EstimateInput'
import ProgressInput from '@/Shared/Inputs/TicketFields/ProgressInput'
import ShortTextInput from '@/Shared/Inputs/TicketFields/ShortTextInput'
import DateInput from '@/Shared/Inputs/TicketFields/DateInput'
import StartDateInput from '@/Shared/Inputs/TicketFields/StartDateInput'
import DueDateInput from '@/Shared/Inputs/TicketFields/DueDateInput'
import TimeInput from '@/Shared/Inputs/TicketFields/TimeInput'
import NumeralInput from '@/Shared/Inputs/TicketFields/NumeralInput'
import DecimalInput from '@/Shared/Inputs/TicketFields/DecimalInput'
import LongTextInput from '@/Shared/Inputs/TicketFields/LongTextInput'
import SeparatorInput from '@/Shared/Inputs/TicketFields/SeparatorInput'
import TimeSpentInput from '@/Shared/Inputs/TicketFields/TimeSpentInput'
import ActivityLog from '@/Shared/Tabs/ActivityLog'
import ParentChildTicketInput from '@/Shared/Inputs/TicketFields/ParentChildTicketInput'

export default {
  name: 'LargeModal',
  components: {
    ActivityLog,
    StatusInput: StatusInput,
    PriorityInput: PriorityInput,
    LayerInput: LayerInput,
    AssigneeInput: AssigneeInput,
    WatchersInput: WatchersInput,
    time_estimateInput: EstimateInput,
    ProgressInput: ProgressInput,
    short_textInput: ShortTextInput,
    DateInput: DateInput,
    start_dateInput: StartDateInput,
    due_dateInput: DueDateInput,
    TimeInput: TimeInput,
    NumeralInput: NumeralInput,
    DecimalInput: DecimalInput,
    long_textInput: LongTextInput,
    SeparatorInput: SeparatorInput,
    time_spentInput: TimeSpentInput,
    ParentChildTicketInput,
  },
  props: {
    ticket: Object,
  },
  data() {
    return {
      activeTab: 'comments',
      media: this.ticket.media,
      title: this.ticket.title,
      showLoggerModal: false,
    }
  },
  methods: {
    async saveTicket(fieldId, fieldVal) {
      await this.$nextTick()
      const field = this.ticket.fields.find(item => item.id === fieldId)
      if (!field) {
        return
      }
      field.value = fieldVal
      const params = this.route().params
      const route = this.route('ticket.update', {
        workspace: this.$page.props.auth.workspace_id,
        project: this.$page.props.project.id,
        ticket: this.ticket.id,
        ...params,
      })
      this.$inertia.put(route, {...field})
    },
    saveTicketImage() {
      const route = this.route('ticket.update', {
        workspace: this.$page.props.auth.workspace_id,
        project: this.$page.props.project.id,
        ticket: this.ticket.id,
      })
      this.$inertia.put(
        route,
        {'media': this.media},
        {
          onSuccess: (data) => {
            let ticket = data.props.tickets.data.find(data => this.ticket.id === data.id)
            this.$set(this, 'media', ticket.media)
          },
        })
    },
    saveTicketTitle() {
      if (this.title === this.ticket.title) return
      const route = this.route('ticket.update', {
        workspace: this.$page.props.auth.workspace_id,
        project: this.$page.props.project.id,
        ticket: this.ticket.id,
      })
      this.$inertia.put(route, {'title': this.title})
    },
    destroy() {
      if (confirm(this.$t('Are you sure you want to delete this ticket?'))) {
        const route = this.route('ticket.destroy', {
          workspace: this.$page.props.auth.workspace_id,
          project: this.$page.props.project.id,
          ticket: this.ticket.id,
        })
        this.$inertia.delete(route, {
          onSuccess: () => {
            this.$emit('close')
          },
        })
      }
    },
    saveTitle(val) {
      this.title = val
    },
    saveEstimateInput(estimate) {
      const field = this.ticket.fields.find(item => item.id === estimate.id)
      field.value = estimate.data
      this.saveTicket(estimate.id)
    },
    deleteChildTicket(id) {
      const index = this.childTickets.findIndex(item => item.id === id)
      this.childTickets.splice(index, 1)
    },
    showParentModal(ticket) {
      const url = this.route('ticket.show', {
        workspace: this.$page.props.auth.workspace_id,
        project: this.$page.props.project.id,
        ticket: ticket.parent_ticket.id,
      })
      const params = new URLSearchParams(window.location.search)
      this.$inertia.get(url, params, {only: ['ticket', 'modal', 'width', 'redirect']})
    },
  },
}
</script>
