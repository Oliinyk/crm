<template>
  <div>
    <div @click="show = true">
      <slot />
    </div>
    <div>
      <div v-show="show" class="fixed top-0 right-0 left-0 bottom-0 opacity-50 z-49" @click="show = false" />
      <div
        class="fixed bg-white top-0 h-full shadow-md w-full sm:max-w-xl z-50 overflow-y-auto transition-all duration-300 ease-linear"
        :class="show ? 'right-0':'-right-full'"
      >
        <form-modal :form="form"
                    :name="$t('Filters')"
                    :show-submit-button="false"
                    :show-cancel-button="false"
                    :show-reset-button="true"
                    :reset-button-name="$t('Reset filters')"
                    @keyup.esc="show=false"
                    @close="show = false"
                    @reset="reset"
        >
          <div v-for="field in fields" :key="field.id" class="w-full">
            <div v-if="field.show" class="w-full">
              <!-- Template Components -->
              <div v-if="[
                $fieldTypes.TYPE_STATUS,
                $fieldTypes.TYPE_PRIORITY,
                $fieldTypes.TYPE_LAYER,
                $fieldTypes.TYPE_ASSIGNEE,
                $fieldTypes.TYPE_WATCHERS,
                $fieldTypes.TYPE_START_DATE,
                $fieldTypes.TYPE_DUE_DATE,
              ].includes(field.name)"
              >
                <component :is="field.name+'-input'"
                           v-model="form[field.name]"
                           class="mb-8"
                           :range="true"
                           :name="field.name"
                           :label="$t(field.name)"
                />
              </div>
              <!-- Changed -->
              <div v-if="field.name === 'changed'">
                <label class="form-label" :for="field.id">{{ $t(field.name) }}:</label>
                <date-picker v-model="form.changed"
                             type="date"
                             range
                             format="YYYY-MM-DD"
                             value-type="YYYY-MM-DD"
                             class="mb-8 datepicker"
                />
              </div>
              <!-- Type -->
              <div v-if="field.name === 'ticket_type'">
                <label class="form-label" :for="field.id">{{ $t(field.name) }}:</label>
                <vselect-search
                  v-model="form.types"
                  class="mb-8"
                  :url="route('ticket-type.search', {workspace: $page.props.auth.workspace_id})"
                />
              </div>
              <!-- Progress -->
              <div v-if="field.name === $fieldTypes.TYPE_PROGRESS">
                <range-input
                  v-model="form.progress"
                  class="mb-8 w-full"
                  :label="$t(field.name)"
                />
              </div>
            </div>
          </div>
        </form-modal>
      </div>
    </div>
  </div>
</template>

<script>
import FormModal from '@/Shared/Modals/FormModal'
import throttle from 'lodash/throttle'
import pickBy from 'lodash/pickBy'
import mapValues from 'lodash/mapValues'

import StatusInput from '@/Shared/Inputs/TicketFields/StatusInput'
import PriorityInput from '@/Shared/Inputs/TicketFields/PriorityInput'
import LayerInput from '@/Shared/Inputs/TicketFields/LayerInput'
import AssigneeInput from '@/Shared/Inputs/TicketFields/AssigneeInput'
import WatchersInput from '@/Shared/Inputs/TicketFields/WatchersInput'
import StartDateInput from '@/Shared/Inputs/TicketFields/StartDateInput'
import DueDateInput from '@/Shared/Inputs/TicketFields/DueDateInput'

export default {
  components: {
    FormModal,
    StatusInput: StatusInput,
    PriorityInput: PriorityInput,
    LayerInput: LayerInput,
    AssigneeInput: AssigneeInput,
    WatchersInput: WatchersInput,
    start_dateInput: StartDateInput,
    due_dateInput: DueDateInput,
  },
  props: {
    ticketTypes: Object,
    fields: {
      type: Array,
      default: null,
    },
    filters: Object,
    search: String,
  },
  data() {
    return {
      show: false,
      form: {
        status: this.filters ? this.filters.status : null,
        search: this.search,
        priority: this.filters ? this.filters.priority : null,
        start_date: this.filters ? this.filters.start_date : null,
        due_date: this.filters ? this.filters.due_date : null,
        layer: this.filters ? this.filters.layer : [],
        assignee: this.filters ? this.filters.assignee : [],
        watchers: this.filters ? this.filters.watchers : [],
        types: this.filters ? this.filters.types : [],
        changed: this.filters ? this.filters.changed : null,
        progress: this.filters ? this.filters.progress : null,
      },
    }
  },
  computed: {
    /**
     * Check if the form has filters
     *
     * @returns {boolean}
     */
    hasFilters() {
      const asArray = Object.entries(this.form)

      let data = asArray.filter(([key, value]) => {
        if (Array.isArray(value)) {
          return value.length
        }
        return value !== null && value !== undefined
      })

      return !!data.length
    },
  },
  watch: {
    /**
     * Check if the form can be shown.
     *
     * @param show
     */
    show(show) {
      let body = document.querySelector('body')
      show ? body.classList.add('overflow-y-hidden') : body.classList.remove('overflow-y-hidden')
    },
    /**
     * Detect form changes.
     */
    form: {
      deep: true,
      handler: throttle(function () {

        let route = this.route('ticket.index', {
          workspace: this.$page.props.auth.workspace_id,
          project: this.$page.props.project.id,
        })
        
        console.log(this.form)
        console.log(pickBy(this.form))
        
        this.$inertia.get(route, pickBy(this.form), {queryStringArrayFormat: 'indices', preserveState: true})
      }, 150),
    },

    /**
     * Detect search query changes.
     *
     * @param search
     */
    search(search) {
      this.form.search = search
    },
  },
  methods: {
    /**
     * Reset all filters.
     */
    reset() {
      this.form = mapValues(this.form, () => null)
    },
  },
}
</script>
