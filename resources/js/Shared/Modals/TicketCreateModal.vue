<template>
  <div>
    <form-modal :form="form"
                :name="$t('Create ticket')"
                @submit="store"
                @close="$emit('close')"
    >
      <select-input v-model="form.ticket_type"
                    :label="$t('Ticket type')"
                    :error="form.errors.ticket_type"
                    class="mb-8 w-full relative-list"
                    :placeholder="$t('Choose ticket type')"
                    :options="ticketTypes.data.map(item => {return{value:item.id,label:item.name}})"
                    @input="onChange"
      />

      <ul v-if="activeTicketType" class="w-full">
        <!-- Title -->
        <li>
          <hr class="w-full pb-8" />
          <text-input v-model="form.title"
                      :error="form.errors.title"
                      class="pb-8 w-full title-block"
                      :label="activeTicketType.title"
                      placeholder="Title"
          />
        </li>
        <li v-for="(field, index) in form.fields" :key="field.id" class="mb-8">
          <!-- Template Components -->
          <div v-if="[
            $fieldTypes.TYPE_STATUS,
            $fieldTypes.TYPE_PRIORITY,
            $fieldTypes.TYPE_LAYER,
            $fieldTypes.TYPE_ASSIGNEE,
            $fieldTypes.TYPE_WATCHERS,
            $fieldTypes.TYPE_ESTIMATE,
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
                       :error="form.errors[`fields.${index}.value`]"
                       :name="field.name"
            />
          </div>
        </li>
        <!-- File input -->
        <li>
          <hr class="w-full pb-6" />
          <multiple-file-input v-model="form.media"
                               :error="form.errors.media"
                               class="pb-3 w-full"
                               type="file"
                               label="Attachments"
                               @processing="data => form.processing = data"
          />
        </li>
      </ul>
    </form-modal>
  </div>
</template>

<script>
import FormModal from '@/Shared/Modals/FormModal'
import StatusInput from '@/Shared/Inputs/TicketFields/StatusInput'
import PriorityInput from '@/Shared/Inputs/TicketFields/PriorityInput'
import LayerInput from '@/Shared/Inputs/TicketFields/LayerInput'
import AssigneeInput from '@/Shared/Inputs/TicketFields/AssigneeInput'
import WatchersInput from '@/Shared/Inputs/TicketFields/WatchersInput'
import FormattedTimeInput from '@/Shared/Inputs/TicketFields/FormattedTimeInput'
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

export default {
  name: 'LargeModal',
  components: {
    FormModal,
    StatusInput: StatusInput,
    PriorityInput: PriorityInput,
    LayerInput: LayerInput,
    AssigneeInput: AssigneeInput,
    WatchersInput: WatchersInput,
    time_estimateInput: FormattedTimeInput,
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
  },
  props: {
    ticketTypes: Object,
  },
  data() {
    return {
      form: this.$inertia.form({
        ticket_type: null,
        title: null,
        fields: [],
        media: [],
      }),
      activeTicketType: null,
    }
  },
  methods: {
    store() {
      const route = this.route('ticket.store', {
        workspace: this.$page.props.auth.workspace_id,
        project: this.$page.props.project.id,
      })
      this.form.post(route, {
        onSuccess: () => {
          this.$emit('close')
        },
      })
    },
    onChange(option) {
      this.form.clearErrors()

      if (option === null) {
        this.activeTicketType = null
        this.form.fields = []
        return
      }

      this.activeTicketType = this.ticketTypes.data.find((ticket) => ticket.id === option)
      this.form.fields = JSON.parse(JSON.stringify(this.activeTicketType.fields))
    },
  },
}
</script>
