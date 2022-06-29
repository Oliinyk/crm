<template>
  <div>
    <label v-if="label && !selected" class="form-label" :class="{ 'label-error': error }" :for="id">{{ label }}:</label>
    <div class="relative flex w-full flex-wrap items-center group">
      <input
        :id="id"
        ref="input"
        v-bind="$attrs"
        class="form-input group-hover:bg-light"
        :class="{
          error: error,
          'pr-10 relative': icon,
          'border-0 text-dark pointer-events-none': selected,
          'placeholder-italic border-transparent': editable,
          'text-lg text-dark font-semibold pl-2': titleInput
        }"
        :type="type"
        :value="value"
        :selected="selected"
        @input="$emit('input', $event.target.value)"
        @blur="$emit('blur')"
      />
      <span
        v-if="icon"
        class="absolute items-center justify-center right-0 pr-4 py-2 cursor-pointer"
        :class="{'opacity-0 group-hover:opacity-100': !alwaysShowIcon}"
        @click="$emit('iconClick', id)"
      >
        <icon :name="icon" class="form-icon"></icon>
      </span>
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
        return `text-input-${this._uid}`
      },
    },
    type: {
      type: String,
      default: 'text',
    },
    value: [String, Number],
    label: String,
    icon: {
      type: String,
      default: null,
    },
    error: String,
    selected: Boolean,
    editable: Boolean,
    alwaysShowIcon: Boolean,
    titleInput: Boolean,
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
  },
}
</script>
