<template>
  <div class="min-h-full bg-primary">
    <Head>
      <title />
    </Head>
    <slot />
    <flash-messages />
  </div>
</template>
<script>
import FlashMessages from '@/Shared/Messages/FlashMessages'

export default {
  components: {
    FlashMessages,
  },
}
</script>
