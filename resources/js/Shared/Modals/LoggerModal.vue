<template>
  <div>
    <form-modal :form="form"
                :name="$t('Work logs')"
                :show-buttons="false"
                @close="$emit('close')"
    >
      <div class="w-2/3 mb-7">
        <div class="flex items-start">
          <div class="flex flex-wrap items-center w-1/2 mr-1.5">
            <formatted-time-input v-model="form.time"
                                  :error="form.errors.time"
                                  class="w-full"
                                  placeholder="3d 6h 45m"
                                  :label="$t($fieldTypes.TYPE_TIME_SPENT)"
            />
          </div>
          <div class="flex flex-wrap items-center w-1/2 ml-1.5">
            <date-input v-model="form.date"
                        :error="form.errors.date"
                        :name="$t($fieldTypes.TYPE_DATE)"
                        :placeholder="$t('Choose date')"
                        class="placeholder-normal"
            />
          </div>
        </div>
      </div>
      <div class="w-full mb-7">
        <div class="flex items-end">
          <div class="w-2/3">
            <long-text-input v-model="form.description"
                             :autosize="true"
                             :error="form.errors.description"
                             :placeholder="$t('Multiline input text field')"
                             :name="$t('Work description')"
            />
          </div>
          <div class="w-1/3">
            <button class="btn-accent inline-flex ml-4 font-semibold" @click="store()">
              {{ $t('Log hours') }}
            </button>
          </div>
        </div>
      </div>
      <div class="w-2/3">
        <Progress-bar :is-error="timeSpent.time_percent > 100" :percent="timeSpent.time_percent" />
        <div class="mt-3 text-gray">
          <div class="flex items-center">
            <p>{{ $t('Logged') }} - {{ timeSpent.time_spent }}.</p>
          </div>
          <div class="flex items-center">
            <p class="pr-1">{{ $t('Initial estimate') }} - {{ timeSpent.time_estimate }}.</p>
          </div>
          <div class="flex items-center">
            <p>{{ $t('Time remaining') }} - {{ timeSpent.time_remaining }}.</p>
          </div>
        </div>
      </div>
      <logger-table :time-entries="timeSpent.logger"
                    class="w-full"
                    @delete="destroy"
      />
    </form-modal>
  </div>
</template>

<script>
import FormModal from '@/Shared/Modals/FormModal'
import FormattedTimeInput from '@/Shared/Inputs/TicketFields/FormattedTimeInput'
import DateInput from '@/Shared/Inputs/TicketFields/DateInput'
import LongTextInput from '@/Shared/Inputs/TicketFields/LongTextInput'
import LoggerTable from '@/Shared/Tables/LoggerTable'
import ProgressBar from '@/Shared/ProgressBar/ProgressBar'

export default {
  name: 'LoggerModal',
  components: {
    FormModal,
    FormattedTimeInput,
    DateInput,
    LongTextInput,
    LoggerTable,
    ProgressBar,
  },
  props: {
    ticket: Object,
    timeSpent: Object,
  },
  data() {
    return {
      form: this.$inertia.form({
        time: null,
        date: null,
        description: null,
        user_id: this.$page.props.auth.user.data.id,
        workspace_id: this.$page.props.auth.workspace_id,
      }),
    }
  },
  methods: {
    store() {
      const params = this.route().params
      let route = this.route('ticket.time-spent.store', {
        workspace: this.$page.props.auth.workspace_id,
        project: this.$page.props.project.id,
        ticket: this.ticket.id,
        ...params,
      })

      this.form.post(route,{
        onSuccess: () => {
          this.form.reset()
        },
      })
    },

    destroy(id) {
      const params = this.route().params
      let route = this.route('ticket.time-spent.destroy', {
        workspace: this.$page.props.auth.workspace_id,
        project: this.$page.props.project.id,
        ticket: this.ticket.id,
        time_spent: id,
        ...params,
      })

      this.$inertia.delete(route)
    },
  },
}
</script>
