<template>
  <div>
    <div :class="['group flex items-center', {'pointer-events-none': disabled}, {'pointer-events-none': disabledMarked}]" @click="toggleCheckbox">
      <input :id="id"
             ref="input"
             v-bind="$attrs"
             :class="['sr-only',{ error: error }]"
             type="checkbox"
             :checked="checkboxValue === trueValue"
             :disabled="disabled"
             :disabledMarked="disabledMarked"
      />
      <icon v-if="disabled" name="checkbox-blank"
            class="fill-accent-dark"
      />
      <icon v-else-if="disabledMarked" name="checkbox-marked"
            class="fill-accent-dark"
      />
      <icon v-else-if="!checkboxValue" name="checkbox-blank"
            class="fill-gray cursor-pointer group-hover:fill-accent-dark"
      />
      <icon v-else-if="checkboxValue" name="checkbox-marked"
            class="fill-secondary cursor-pointer group-hover:fill-accent-dark"
      />
      <label v-if="label" class="form-label inline m-0 cursor-pointer ml-2">{{ label }}</label>
    </div>
    <div v-if="error" class="form-error">{{ error }}</div>
  </div>
</template>

<script>

export default {
  inheritAttrs: false,
  props: {
    id: {
      type: String,
      default() {
        return `checkbox-input-${this._uid}`
      },
    },
    value: [Boolean, String, Array],
    label: String,
    error: String,
    disabled: Boolean,
    disabledMarked: Boolean,
    trueValue: {
      type: [String, Boolean],
      default: true,
    },
    falseValue: {
      type: [String, Boolean],
      default: false,
    },
  },
  data() {
    return {
      checkboxValue: null,
    }
  },
  watch: {
    checkboxValue(val) {
      this.$emit('input', val)
    },
  },
  created() {
    this.checkboxValue = this.value
  },
  methods: {
    toggleCheckbox() {
      this.checkboxValue = this.checkboxValue === this.trueValue ? this.falseValue : this.trueValue
    },
    focus() {
      this.$refs.input.focus()
    },
    select() {
      this.$refs.input.select()
    },
    setSelectionRange(start, end) {
      this.$refs.input.setSelectionRange(start, end)
    },
  },
}
</script>
