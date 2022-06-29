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
    <text-input
      :value="currentValue"
      :class="{
        'flex items-center rounded cursor-pointer w-full placeholder-italic': editable,
        'w-full': !editable
      }"
      :error="error"
      :editable="editable"
      :placeholder="placeholder"
      @blur="save"
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
