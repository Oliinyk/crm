<template>
  <div class="w-full">
    <label
      :class="{
        'text-gray font-bold min-w-100': editable,
        'form-label': !editable,
        'form-error': error
      }"
    >
      <span>{{ name }}:</span>
    </label>
    <date-picker
      :value="currentValue"
      class="datepicker"
      :class="{
        'editable': editable,
        'error': error
      }"
      format="YYYY-MM-DD"
      value-type="YYYY-MM-DD"
      :type="type"
      :append-to-body="false"
      :placeholder="placeholder"
      :range="range"
      @close="save"
      @input="change"
    />
    <div v-if="error" class="form-error">{{ error }}</div>
  </div>
</template>

<script>

export default {
  props: {
    value: [String, Array],
    error: String,
    type: {
      type: String,
      default: 'date',
    },
    placeholder: {
      type: String,
      default: '',
    },
    editable: Boolean,
    range: {
      type: Boolean,
      default: false,
    },
    name: String,
  },
  data() {
    return {
      currentValue: this.value,
    }
  },
  watch: {
    value() {
      this.currentValue = this.value
    },
  },
  methods: {
    change(val) {
      this.currentValue = val
    },
    save() {
      if(this.value !== this.currentValue) {
        this.$emit('input', this.currentValue)
      }
    },
  },
}
</script>
