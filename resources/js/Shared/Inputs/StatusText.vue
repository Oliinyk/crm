<template>
  <span class="px-3 inline-flex items-center rounded-2xl whitespace-nowrap"
        :class="{
          'bg-red-opacity text-red-dark open': status==='open',
          'bg-orange-opacity text-orange-dark in-progress': status==='in_progress',
          'bg-primary-opacity text-primary-dark resolved': status==='resolved'
        }"
  >
    {{ $t(status) }}
  </span>
</template>

<script>

export default {
  name: 'StatusText',
  props: {
    status: String,
  },
}
</script>
