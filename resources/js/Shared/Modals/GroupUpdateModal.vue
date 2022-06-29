<template>
  <div>
    <form-modal :form="form"
                :name="$t('Update Group of users')"
                :submit-button-name="$t('Update')"
                :show-delete-button="true"
                @close="$emit('close')"
                @delete="destroy"
                @submit="store"
    >
      <text-input v-model="form.name" :error="form.errors.name" class="pb-7 w-full" :label="$t('Group Name')" />
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
  props: {
    group: Object,
  },
  data() {
    return {
      form: this.$inertia.form({
        name: this.group.name,
      }),
    }
  },
  methods: {
    store() {
      this.form.put(this.route('group.update', {workspace: this.$page.props.auth.workspace_id, group: this.group.id}), {
        onSuccess: () => {
          this.$emit('close')
        },
      })
    },
    destroy() {
      if (confirm(this.$t('Are you sure you want to delete this group?'))) {
        this.$inertia.delete(this.route('group.destroy', {
          workspace: this.$page.props.auth.workspace_id,
          group: this.group.id,
        }), {
          onSuccess: () => {
            this.$emit('close')
          },
        })
      }
    },
  },
}
</script>
