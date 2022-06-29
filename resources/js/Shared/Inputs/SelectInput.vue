<template>
  <div>
    <label v-if="label" class="form-label" :class="{'label-error':error}" :for="id">{{ label }}:</label>
    <v-select v-model="selected"
              v-bind="$attrs"
              class=" form-select pl-0.5 py-0.5 flex items-center"
              :class="{'error':error}"
              :options="options"
              :placeholder="placeholder"
              :reduce="reduce"
    />
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
        return `select-input-${this._uid}`
      },
    },
    value: [String, Number, Boolean],
    label: String,
    error: String,
    options: Array,
    placeholder: String,
  },
  data() {
    return {
      selected: this.value,
    }
  },
  watch: {
    selected(selected) {
      this.$emit('input', selected)
    },
  },
  methods: {
    focus() {
      this.$refs.input.focus()
    },
    select() {
      this.$refs.input.select()
    },
    reduce(data) {
      if (typeof data === 'object') {
        return data.value
      }
      return data
    },
  },
}
</script>
