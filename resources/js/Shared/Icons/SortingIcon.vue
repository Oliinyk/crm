<template>
  <span v-if="true" class="flex flex-col ml-1 mt-0.5 h-3.5 justify-between cursor-pointer">
    <icon name="sorting-arrow"
          class="block fill-gray-light"
          :class="{'fill-gray' : direction === 'asc' && isActive}"
    />
    <icon name="sorting-arrow"
          class="block transform rotate-180 fill-gray-light"
          :class="{'fill-gray' : direction === 'desc' && isActive}"
    />
  </span>
</template>
<script>
export default {
  props: {
    direction: String,
    isActive: Boolean,
  },
}
</script>