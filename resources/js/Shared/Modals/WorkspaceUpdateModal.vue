<template>
  <div>
    <form-modal :form="form"
                :name="$t('Update Workspace')"
                :submit-button-name="$t('Update')"
                :show-delete-button="true"
                @submit="update"
                @close="$emit('close')"
                @delete="destroy"
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
  name: 'WorkspaceUpdateModal',
  components: {
    FormModal,
  },
  props: {
    workspace: Object,
  },
  data() {
    return {
      form: this.$inertia.form({
        _method: 'put',
        name: this.workspace.name,
        plan: this.workspace.plan,
        photo: this.workspace.image,
      }),
    }
  },
  methods: {
    update() {
      this.form.post(this.route('workspace.update', this.workspace.id), {
        onSuccess: () => {
          this.$emit('close')
        },
      })
    },
    destroy() {
      if (confirm(this.$t('Are you sure you want to delete this workspace?'))) {
        this.$inertia.delete(this.route('workspace.destroy', this.workspace.id), {
          onSuccess: () => {
            this.$emit('close')
          },
        })
      }
    },
  },
}
</script>
