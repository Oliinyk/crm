<template>
  <div>
    <div v-if="!src" class="rounded-full text-dark flex items-center justify-center mr-2"
         :class="[addSize(), primaryBg ? 'bg-primary-light':'bg-accent' ]"
    >
      {{ fullName.substring(0, 1) }}
    </div>
    <img v-else :src="src" :alt="fullName"
         class="rounded-full flex items-center justify-center mr-2"
         :class="addSize()"
    />
  </div>
</template>

<script>

export default {
  props: {
    fullName: String,
    src: {
      type: String,
      default: '',
    },
    size: {
      type: String,
      default: 'normal',
    },
    primaryBg: {
      type: Boolean,
      default: false,
    },
  },
  methods: {
    addSize() {
      if (this.size === 'small') {
        return 'w-7 h-7 text-sm'
      }
      if (this.size === 'big') {
        return 'w-12 h-12 text-lg'
      }
      return 'w-9 h-9'
    },
  },
}
</script>
