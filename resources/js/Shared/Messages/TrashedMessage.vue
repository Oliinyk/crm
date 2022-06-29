<template>
  <div class="p-4 bg-orange rounded flex items-center justify-between max-w-3xl w-full">
    <div class="flex items-center">
      <icon name="trash" class="flex-shrink-0 w-4 h-4 fill-light mr-2" />
      <div class="text-sm font-medium text-light">
        <slot />
      </div>
    </div>
    <button class="text-sm text-light hover:underline" tabindex="-1" type="button" @click="$emit('restore')">
      {{ $t('Restore') }}
    </button>
  </div>
</template>

<script>

export default {}
</script>
