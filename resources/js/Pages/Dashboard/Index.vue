<template>
  <div>
    <Head :title="$t('Dashboard')" />
    <h1 class="mb-8 font-bold text-dark text-xl">{{ $t('Dashboard') }}</h1>
    <p class="mb-8 leading-normal">{{ $t('Hey there! Welcome to Brick CRM') }}</p>
  </div>
</template>

<script>
import Layout from '@/Shared/Layouts/Layout'

export default {
  layout: Layout,
}
</script>
