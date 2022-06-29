<template>
  <div>
    <div v-if="!src" class="w-9 h-9 rounded-sm bg-secondary text-accent flex items-center justify-center mr-2">
      {{ name.substring(0, 1) }}
    </div>
    <img v-else :src="src" :alt="name" class="w-7 h-7 object-contain flex items-center justify-center mr-2" />
  </div>
</template>


<script>

export default {
  props: {
    name: String,
    src: {
      type: String,
      default: '',
    },
  },
}
</script>
