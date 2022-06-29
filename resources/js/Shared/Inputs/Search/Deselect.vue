<template>
  <span @click="deselect">
    <icon name="close" class="w-4 h-4 fill-gray" />
  </span>
</template>

<script>
export default {
  name: 'Deselect',
  methods: {
    deselect() {
      this.$parent.$emit('deselect')
    },
  },
}
</script>