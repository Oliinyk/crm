<template>
  <div>
    <label :class="{
      'text-gray font-bold min-w-100': editable,
      'form-label': !editable,
      'form-error': error
    }"
    >
      {{ $t($fieldTypes.TYPE_TIME_SPENT) }}:
    </label>
    <div class="flex items-center px-4 py-2 w-full">
      <p v-if="value.time_spent" class="pr-4">{{ value.time_spent }}</p>
      <p v-if="isOnlyText" class="text-secondary cursor-pointer" @click="showLoggerModal()">
        {{ $t('Open time logger') }}
      </p>
    </div>
    <div v-if="error" class="form-error">{{ error }}</div>
  </div>
</template>

<script>

export default {
  props: {
    ticket: Object,
    value: Object,
    error: String,
    type: {
      type: String,
      default: 'time',
    },
    placeholder: {
      type: String,
      default: '',
    },
    editable: Boolean,
    isOnlyText: Boolean,
  },
  data() {
    return {}
  },
  methods: {
    showLoggerModal() {
      const url = this.route('ticket.time-spent.index', {
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
