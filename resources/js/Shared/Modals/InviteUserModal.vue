<template>
  <div>
    <form-modal :form="form"
                :name="$t('Invite user to your workspace')"
                :submit-button-name="$t('Invite')"
                @submit="store"
                @close="$emit('close')"
    >
      <text-input id="client-email"
                  v-model="form.email"
                  :error="form.errors.email"
                  class="pb-7 w-full"
                  :label="$t('User email')"
                  :placeholder="$t('Input user email')"
      />
    </form-modal>
  </div>
</template>

<script>
import FormModal from '@/Shared/Modals/FormModal'

export default {
  name: 'LargeModal',
  components: {
    FormModal,
  },
  data() {
    return {
      form: this.$inertia.form({
        email: null,
      }),
    }
  },
  methods: {
    store() {
      this.form.post(this.route('invitation.store', {workspace: this.$page.props.auth.workspace_id}), {
        onSuccess: () => {
          this.$emit('close')
        },
      })
    },
  },
}
</script>
