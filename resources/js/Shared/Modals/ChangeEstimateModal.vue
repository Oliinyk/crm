<template>
  <div>
    <form-modal :form="form"
                :name="$t('Estimate log')"
                :show-buttons="false"
                @close="$emit('close')"
    >
      <div class="w-full mb-7">
        <div class="flex flex-wrap">
          <div class="w-full sm:w-2/3">
            <formatted-time-input v-model="form.time"
                                  :error="form.errors.time"
                                  class="w-full"
                                  :placeholder="$t('Add estimate in correct format (4h 35m)')"
                                  @error="form.errors.time"
            />
          </div>
          <div v-if="!timeEstimate.logger.data.length" class="w-full sm:w-1/3 mt-6">
            <button class="btn-accent inline-flex font-semibold sm:ml-4 mt-4 sm:mt-0" @click="store()">
              {{ $t('Add estimate') }}
            </button>
          </div>
        </div>
      </div>

      <div v-if="timeEstimate.logger.data.length" class="w-full mb-7">
        <div class="flex flex-wrap items-end">
          <div class="w-full sm:w-2/3">
            <long-text-input v-model="form.description"
                             :error="form.errors.description"
                             :autosize="true"
                             :placeholder="$t('Multiline input text field')"
                             :name="$t('Reason of estimate change')"
            />
          </div>
          <div class="w-full sm:w-1/3">
            <button class="btn-accent inline-flex font-semibold sm:ml-4 mt-4 sm:mt-0" @click="store()">
              {{ $t('Change estimate') }}
            </button>
          </div>
        </div>
      </div>

      <p>{{ $t('Estaimate change log') }}</p>

      <estimate-table :time-entries="timeEstimate.logger" class="w-full" />
    </form-modal>
  </div>
</template>

<script>
import FormModal from '@/Shared/Modals/FormModal'
import FormattedTimeInput from '@/Shared/Inputs/TicketFields/FormattedTimeInput'
import LongTextInput from '@/Shared/Inputs/TicketFields/LongTextInput'
import EstimateTable from '@/Shared/Tables/EstimateTable'

export default {
  name: 'ChangeEstimateModal',
  components: {
    FormModal,
    FormattedTimeInput,
    LongTextInput,
    EstimateTable,
  },
  props: {
    timeEstimate: Object,
    ticket: Object,
  },
  data() {
    return {
      form: this.$inertia.form({
        time: this.timeEstimate.time_estimate,
        description: null,
      }),
    }
  },
  methods: {
    store() {
      const params = this.route().params
      let route = this.route('ticket.time-estimate.store', {
        workspace: this.$page.props.auth.workspace_id,
        project: this.$page.props.project.id,
        ticket: this.ticket.id,
        ...params,
      })

      this.form.post(route, {
        onSuccess: () => {
          this.form.reset()
        },
      })
    },
  },
}
</script>
