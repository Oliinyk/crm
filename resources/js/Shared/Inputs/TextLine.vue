<template>
  <div>
    <label v-if="label" class="form-label" :for="id">{{ label }}:</label>
    <div class="relative flex w-full flex-wrap items-stretch">
      <div :id="id" class="text-dark">
        {{ value }}
      </div>
      <span v-if="icon" class="absolute items-center justify-center right-0 pr-4 py-2">
        <icon :name="icon" class="form-icon"></icon>
      </span>
    </div>
  </div>
</template>

<script>

export default {
  inheritAttrs: false,
  props: {
    id: {
      type: String,
      default() {
        return `text-line-${this._uid}`
      },
    },
    value: String,
    label: String,
    icon: {
      type: String,
      default: null,
    },
  },
}
</script>
