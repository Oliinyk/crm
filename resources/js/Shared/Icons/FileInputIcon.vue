<template>
  <div class="mr-2">
    <icon v-if="!src" name="file" class="w-12 h-12 fill-secondary"></icon>
    <img v-else :src="src" alt="file image" class="w-12 h-12 object-contain" />
  </div>
</template>


<script>

export default {
  components: {},
  props: {
    src: {
      type: String,
      default: '',
    },
  },
}
</script>
