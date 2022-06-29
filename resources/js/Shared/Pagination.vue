<template>
  <div>
    <div class="flex justify-between text-sm text-gray">
      <div>
        {{ $t('Showing') }}
        <span v-if="!total" class="font-bold">0</span>
        <span v-else class="font-bold">{{ from }} - {{ to }}</span>
        {{ $t('of') }}
        <span class="font-bold">{{ total }}</span>
      </div>
      <div class="flex items-center">
        <icon v-if="currentPage === 1" name="chevron-double-left" class="fill-gray"></icon>
        <Link v-else :href="firstPageUrl">
          <icon name="chevron-double-left" class="fill-secondary"></icon>
        </Link>
        <icon v-if="!prevPageUrl" name="chevron-left" class="fill-gray"></icon>
        <Link v-else :href="prevPageUrl">
          <icon name="chevron-left" class="fill-secondary"></icon>
        </Link>
        <span class="font-bold">{{ currentPage }}</span>
        &nbsp;{{ $t('of') }}&nbsp;
        <span class="font-bold">{{ lastPage }}</span>
        <icon v-if="!nextPageUrl" name="chevron-right" class="fill-gray"></icon>
        <Link v-else :href="nextPageUrl">
          <icon name="chevron-right" class="fill-secondary"></icon>
        </Link>
        <icon v-if="currentPage === lastPage" name="chevron-double-right" class="fill-gray"></icon>
        <Link v-else :href="lastPageUrl">
          <icon name="chevron-double-right" class="fill-secondary"></icon>
        </Link>
      </div>
    </div>
  </div>
</template>

<script>

export default {
  components: {},
  props:
    {
      currentPage: Number,
      lastPage: Number,
      firstPageUrl: String,
      lastPageUrl: String,
      prevPageUrl: String,
      nextPageUrl: String,
      from: Number,
      to: Number,
      total: Number,
    },
}
</script>
