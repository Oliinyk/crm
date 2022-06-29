<template>
  <div class="flex-wrap">
    <div class="w-full flex items-center">
      <label
        v-if="!selected"
        :class="{
          'text-gray font-bold min-w-100': editable,
          'form-label': !editable
        }"
      >
        <span>{{ $t($fieldTypes.TYPE_ESTIMATE) }}:</span>
      </label>

      <div class="w-full">
        <div class="relative flex w-full flex-wrap items-stretch group py-2 px-4">
          <div v-if="value" class="flex items-center" @click="showChangeModal">
            <p>{{ value }}</p>
            <p class="text-secondary cursor-pointer pl-4">{{ $t('Change') }}</p>
          </div>
          <p v-else class="text-secondary cursor-pointer" @click="showChangeModal">
            {{ $t('Add estimate') }}
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>

export default {
  inheritAttrs: false,
  props: {
    ticket: Object,
    type: {
      type: String,
      default: 'text',
    },
    label: String,
    value: [String, Number],
    error: String,
    selected: Boolean,
    editable: Boolean,
  },
  methods: {
    showChangeModal() {
      const url = this.route('ticket.time-estimate.index', {
        workspace: this.$page.props.auth.workspace_id,
        project: this.$page.props.project.id,
        ticket: this.ticket.id,
      })

      const params = new URLSearchParams(window.location.search)

      this.$inertia.get(url, params)
    },
  },
}
</script>
