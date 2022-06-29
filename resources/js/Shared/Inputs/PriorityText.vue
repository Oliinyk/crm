<template>
  <span class="inline-flex items-center">
    {{ $t(priority) }}
    <icon
      :name="priority"
      class="w-6 ml-2.5"
      :class="classes(priority)"
    />
  </span>
</template>

<script>

export default {
  name: 'PriorityText',
  props: {
    priority: String,
  },
  methods: {
    classes(label) {
      return {
        'fill-gray': label === 'low',
        'fill-orange': label === 'medium',
        'fill-red': label === 'high',
      }
    },
  },
}
</script>
