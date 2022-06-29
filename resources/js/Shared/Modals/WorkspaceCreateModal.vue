<template>
  <div>
    <form-modal :form="form"
                :name="$t('Create Workspace')"
                @submit="store"
                @close="$emit('close')"
    >
      <text-input v-model="form.name"
                  :error="form.errors.name"
                  :placeholder="$t('Type a name for your workspace')"
                  class="pb-7 w-full"
                  label="Name"
      />
      <file-input v-model="form.photo"
                  :error="form.errors.photo"
                  class="pb-7 w-full"
                  type="file"
                  accept="image/*"
                  :extensions="['jpg', 'png', 'jpeg']"
                  :label="$t('Photo')"
                  @processing="data => form.processing = data"
      />
      <select-input v-model="form.plan"
                    class="w-full mb-7"
                    :label="$t('Plan')"
                    :error="form.errors.plan"
                    :options="[ $t('Personal'), $t('Team')]"
                    :placeholder="$t('Choose a plan that fits your needs')"
      />
    </form-modal>
  </div>
</template>

<script>
import FormModal from '@/Shared/Modals/FormModal'

export default {
  name: 'WorkspaceCreateModal',
  components: {
    FormModal,
  },
  data() {
    return {
      form: this.$inertia.form({
        name: null,
        plan: null,
        photo: null,
      }),
    }
  },
  methods: {
    store() {
      this.form.post(this.route('workspace.store', {
        workspace: this.$page.props.auth.workspace_id,
      }), {
        onSuccess: () => {
          this.$emit('close')
        },
      })
    },
  },
}
</script>
