<template>
  <p class="text-gray text-xs font-bold uppercase px-4">
    <span v-if="searchResults.length" class="flex pt-3 pb-2">
      {{ text }}
    </span>
  </p>
</template>

<script>
export default {
  props: {
    searchResults: Array,
    searchForm: Object,
    meta: Object,
  },
  computed: {
    text() {
      if (this.searchForm.search && this.meta) {
        return `${this.$t('Search results')} (${this.searchResults.length} ${this.$t('of')} ${this.meta.total})`
      }

      return this.$t('Suggestions')
    },
  },
}
</script>

