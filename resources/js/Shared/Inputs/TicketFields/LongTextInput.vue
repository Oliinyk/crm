<template>
  <div class="flex flex-wrap">
    <label
      :class="{
        'text-gray font-bold min-w-100': editable,
        'form-label': !editable,
        'form-error': error
      }"
      class="mb-2"
    >
      <span>{{ name }}:</span>
    </label>
    <textarea-input
      :value="currentValue"
      :error="error"
      :class="{
        'flex items-center rounded cursor-pointer w-full placeholder-italic': editable,
        'w-full': !editable
      }"
      :editable="editable"
      :placeholder="placeholder"
      :autosize="autosize"
      :buttons-control="buttonsControl"
      @input="change"
    />
  </div>
</template>

<script>

export default {
  props: {
    value: String,
    error: String,
    placeholder: {
      type: String,
      default: '',
    },
    editable: Boolean,
    autosize: {
      type: Boolean,
      default: false,
    },
    buttonsControl: {
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
      this.$emit('input', this.currentValue)
    },
  },
}
</script>
