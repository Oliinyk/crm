<template>
  <div :class="{'flex-wrap': isOnlyText}">
    <div class="w-full" :class="{'flex items-center': isOnlyText}">
      <label
        v-if="!selected"
        :class="{
          'text-gray font-bold min-w-100': editable,
          'form-label': !editable,
          'label-error': error || estimateError
        }"
        :for="id"
      >
        <span>{{ $t($fieldTypes.TYPE_ESTIMATE) }}:</span>
      </label>
      <div class="w-full">
        <div class="relative flex w-full flex-wrap items-stretch group">
          <input
            :id="id"
            ref="input"
            v-bind="$attrs"
            v-model="estimate"
            class="form-input group-hover:bg-light placeholder-normal"
            :class="{
              'error' : error || estimateError,
              'border-0 text-dark pointer-events-none': selected,
              'border-transparent': editable
            }"
            :type="type"
            :value="value"
            :placeholder="placeholder"
            @input="changeEstimate($event.target.value)"
            @blur="formatEstimate"
          />
        </div>
        <div v-if="estimateError || error" class="form-error">{{ estimateError || error }}</div>
      </div>
    </div>
  </div>
</template>

<script>
import estimateFormat from '@/Setup/estimateFormat'

export default {

  inheritAttrs: false,
  props: {
    id: {
      type: String,
      default() {
        return `estimate-input-${this._uid}`
      },
    },
    type: {
      type: String,
      default: 'text',
    },
    label: String,
    value: [String, Number],
    error: String,
    selected: Boolean,
    editable: Boolean,
    idField: Number,
    isOnlyText: Boolean,
    placeholder: String,
  },
  data() {
    return {
      estimateError: null,
      estimate: '',
    }
  },
  watch: {
    value() {
      this.estimate = this.value
    },
  },
  mounted() {
    this.estimate = this.value
  },
  methods: {
    focus() {
      this.$refs.input.focus()
    },
    select() {
      this.$refs.input.select()
    },
    setSelectionRange(start, end) {
      this.$refs.input.setSelectionRange(start, end)
    },
    changeEstimate(val) {
      this.$emit('input', val)
      const DATE_REG = /[^0-9, dhmDHM]/
      if (DATE_REG.test(this.estimate)) { //checking characters for compliance with the rule
        this.$emit('error', true)
      }
      if (this.estimate.search(/[^0-9, ]/) === -1) { //check for numbers
        this.$emit('error', true)
      }
    },
    //estimate validation
    formatEstimate() {
      this.estimateError = null
      let workingHours = this.$page.props.project.working_hours
      let minSelected = estimateFormat.toMin(this.estimate, workingHours)
      if (minSelected === -1) {
        this.minSelected = 0
        this.estimateError = 'Use the format: 3d 6h 45m'
      }
      this.$emit('minSelected', minSelected)
      this.$emit('error', false)
      this.estimate = estimateFormat.toFormat(this.estimate, workingHours)
      if (this.estimate === -1) {
        this.estimate = null
        this.estimateError = 'Use the format: 3d 6h 45m'
      }
      this.$emit('saveEstimateInput', {data: this.estimate, id: this.idField})
    },
  },
}
</script>
