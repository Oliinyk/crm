<template>
  <button :disabled="loading" class="flex items-center btn-primary" :class="{'bg-primary-light pointer-events-none': disable}">
    <div v-if="loading" class="btn-spinner mr-2" />
    <slot />
  </button>
</template>

<script>
export default {
  props: {
    loading: Boolean,
    disable: Boolean,
  },
}
</script>
