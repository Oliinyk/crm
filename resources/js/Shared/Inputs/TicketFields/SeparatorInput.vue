<template>
  <div :class="{'flex-wrap -mr-6':editable}">
    <hr class="w-full pb-2" />
    <div class="w-full" :class="editable ? 'pb-4':'pb-6'">
      <p v-if="name" class="text-xs font-bold uppercase text-dark">{{ name }}</p>
      <p v-else class="text-xs font-bold uppercase text-dark">{{ $t($fieldTypes.TYPE_SEPARATOR) }}</p>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    editable: Boolean,
    name: String,
  },
}
</script>
