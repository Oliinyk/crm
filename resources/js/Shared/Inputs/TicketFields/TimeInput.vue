<template>
  <div>
    <label :class="{
      'text-gray font-bold min-w-100': editable,
      'form-label': !editable,
      'form-error': error
    }"
    >
      {{ name }}:
    </label>
    <date-picker
      :value="currentValue"
      class="datepicker"
      :class="{
        'editable': editable,
        'error': error,
      }"
      value-type="format"
      :append-to-body="false"
      :placeholder="placeholder"
      :type="type"
      @close="save"
      @input="change"
    />
    <div v-if="error" class="form-error">{{ error }}</div>
  </div>
</template>

<script>

export default {
  props: {
    value: String,
    error: String,
    type: {
      type: String,
      default: 'time',
    },
    placeholder: {
      type: String,
      default: '',
    },
    editable: Boolean,
    name: String,
  },
  data() {
    return {
      currentValue: this.value,
    }
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
